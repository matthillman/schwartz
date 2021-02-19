import { GuildMember, User } from 'discord.js';
import { inject, injectable } from 'inversify';
import { Pool } from 'pg';
import { TYPES } from '../ioc/types';

export enum PatronLevel {
    none,
    ridiculous,
    ludicrous,
    plaid,
}

export interface CombinedPatronLevel {
    effectiveLevel: PatronLevel;
    guildLevel: PatronLevel;
    userLevel: PatronLevel;
}

interface PatronQueryResult {
    discord_id: string;
    name: string;
    guild_patron_level: number;
    patron_level: number;
    schwartz: number;
}
const ROLE_MAP = new Map([
    [PatronLevel.plaid, '793338563398991872'],
    [PatronLevel.ludicrous, '793338106346471424'],
    [PatronLevel.ridiculous, '793338361615745036'],
]);

@injectable()
export class Patron {
    private cache = {};
    constructor(
        @inject(TYPES.DBPool) private dbPool: Pool,
    ) { }

    async updatePatronLevelFor(member: GuildMember) {
        let patronLevel: PatronLevel = PatronLevel.none;
        for (const [level, id] of ROLE_MAP.entries()) {
            if (member.roles.cache.find(r => r.id === id)) {
                patronLevel = level;
                break;
            }
        }

        const db = await this.dbPool.connect();
        const result = await db.query(`
        insert into patron_level (discord_id, patron_level, created_at, updated_at) values ($1::text, $2::smallint, to_timestamp($3), to_timestamp($3))
        on conflict (discord_id) do update set patron_level = excluded.patron_level, updated_at = excluded.updated_at
        `, [member.id, patronLevel, Math.floor(Date.now() / 1000)]);
        // console.debug(result);
        db.release();

        if (this.cache[member.id]) {
            delete this.cache[member.id];
        }

        console.log(`[MEMBER UPDATE] Member ${member.nickname ?? member.displayName} (${member.id}) updated to role level ${patronLevel}`);
    }

    async patronLevelFor(member: GuildMember | User) {
        if (this.cache[member.id]) {
            return this.cache[member.id];
        }
        const db = await this.dbPool.connect();
        const result = await db.query<PatronQueryResult>(`
            with guild_patrons as (
                select guilds.id, max(case when guilds.schwartz then 3 else 0 end) as schwartz, sum(patron_level) as patron_level
                from guilds
                join members on members.guild_id = guilds.id
                left join ally_code_map on members.ally_code = ally_code_map.ally_code
                left join patron_level on ally_code_map.discord_id = patron_level.discord_id
                group by guilds.id
            )
            select distinct
                patron_level.discord_id,
                users.name,
                patron_level.patron_level,
                max(guild_patrons.schwartz) over (partition by patron_level.discord_id) as schwartz,
                coalesce(max(guild_patrons.patron_level) over (partition by patron_level.discord_id), 0)::integer as guild_patron_level
            from patron_level
            left join users on users.discord_id = patron_level.discord_id
            join ally_code_map on ally_code_map.discord_id = patron_level.discord_id
            join members on members.ally_code = ally_code_map.ally_code
            left join guild_patrons on members.guild_id = guild_patrons.id
            where patron_level.discord_id = $1::text;
        `, [member.id]);
        // console.debug(result);

        const memberInfo = result.rows.find(r => r.discord_id === member.id);

        if (!memberInfo) { return { effectiveLevel: PatronLevel.none, userLevel: PatronLevel.none, guildLevel: PatronLevel.none }; }

        const effectiveLevel = Math.min(Math.max(memberInfo.patron_level, memberInfo.schwartz, memberInfo.guild_patron_level), 3) as PatronLevel;

        console.log(`[PATRON CHECK] Member ${member.id} has level ${effectiveLevel}`);

        db.release();

        this.cache[member.id] = { effectiveLevel, userLevel: memberInfo.patron_level, guildLevel: memberInfo.guild_patron_level }

        return this.cache[member.id];
    }
}
