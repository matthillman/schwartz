import { Message, Role, Snowflake } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { PatronLevel } from '../services/patron';
import { Settings } from '../services/settings';
import { BaseCommand, NoPermissionsError, HelpText, CommandCategory } from './command';
import { PitCommand, PitOpen, PitPost, PitStarting, PitHolding, PitSetPostThreshold, PitStatus, PitSetRole, PitClose, PitHelp } from './pit.commands';

export interface PitHold {
    id: Snowflake;
    name: string;
    amount: number;
};

export interface PitSettings {
    phase: number;
    holding: PitHold[];
    bossRole: string;
    postThreshold: number;
    starting: number;
    notificationSent: boolean;
};

const defaultPitSettings: PitSettings = {
    phase: 0,
    holding: [],
    bossRole: 'Pit Boss',
    postThreshold: 105,
    starting: 100,
    notificationSent: false,
};

@injectable()
export class Pit extends BaseCommand {
    name = 'pit';
    aliases: string[] = [];
    patronLevel = PatronLevel.ludicrous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Challenge Tier Pit Helper. See *usage* command for more information',
        usage: 'pit usage',
    };

    private commands: PitCommand[];

    constructor(
        @inject(TYPES.Settings) private settings: Settings,
        @inject(TYPES.PitOpen) open: PitOpen,
        @inject(TYPES.PitStarting) starting: PitStarting,
        @inject(TYPES.PitPost) post: PitPost,
        @inject(TYPES.PitHolding) hold: PitHolding,
        @inject(TYPES.PitSetRole) setRole: PitSetRole,
        @inject(TYPES.PitSetPostThreshold) setPostThreshold: PitSetPostThreshold,
        @inject(TYPES.PitStatus) status: PitStatus,
        @inject(TYPES.PitClose) close: PitClose,
        @inject(TYPES.PitHelp) private pitHelp: PitHelp,
    ) {
        super();

        this.commands = [
            open,
            starting,
            post,
            hold,
            setRole,
            setPostThreshold,
            status,
            close,
            pitHelp,
        ];
    }

    async execute(args: string[], message: Message): Promise<boolean> {
        const command = (args.shift() || '').toLowerCase();

        const guildSettings = this.settings.guildSettings(message.guild);

        const roles = await message.guild.roles.fetch();
        const adminRole = roles.cache.array().find((r: Role) => r.name.toLowerCase() === guildSettings.adminRole.toLowerCase());

        const pitSettings = this.settings.getSettings<PitSettings>(message.channel.id, defaultPitSettings);
        const roleSearch = (pitSettings.bossRole ?? defaultPitSettings.bossRole).toLowerCase();
        const pitBossRole = roles.cache.array().find((r: Role) => r.name.toLowerCase() === roleSearch);

        if (command !== 'setRole'.toLowerCase() && !pitBossRole) {
            await message.reply(`Looked for boss role "${pitSettings.bossRole || 'Pit Boss'}", but I didn't find a role with that name. You must define a boss role (${this.settings.prefix}pit setRole <some role name that exists>) before you can use this feature.`);
        }

        const pitBossMention = pitBossRole ?`<@&${pitBossRole.id}>: ` : '';
        const adminMention = adminRole ? `<@&${adminRole.id}>` : 'Admin';
        const isBoss = pitBossRole && message.member.roles.cache.has(pitBossRole.id);
        const currentPhase = pitSettings.phase ?? 0;

        const commandInfo = {
            pitBossMention,
            pitBossRole,
            adminMention,
            adminRole,
            isBoss,
            currentPhase,
            pitSettings,
        };

        for (const cmd of this.commands) {
            try {
                const handled = await cmd.execute([command, ...args], message, commandInfo);
                if (handled) { return true; }
            } catch (err) {
                if (err instanceof NoPermissionsError) {
                    // We found the right command, just didn't have permissions
                    return true;
                }
                console.error(`[PIT] ${err}`);
            }
        }
        console.log(`Running HELP`);
        // They picked something that didn't exist, send the help
        await this.pitHelp.execute(['help', ...args], message, commandInfo);
        return true;
    }
}

