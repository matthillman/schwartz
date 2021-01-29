import { Message, Role } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { Settings } from './settings';

export interface Permission {
    level: PermLevel;
    name: string;
    check(message: Message): Promise<boolean>;
}

export enum PermLevel {
    user = 0,
    moderator = 2,
    administrator = 3,
    owner = 4,
    support = 8,
    botAdmin = 9,
    frax = 10,
};

@injectable()
export class Permissions {
    public readonly levels: Permission[];

    constructor(
        @inject(TYPES.Settings) settings: Settings,
    ) {
        this.levels = [
            {
                level: PermLevel.user,
                name: 'User',
                check: async () => true,
            },
            {
                level: PermLevel.moderator,
                name: 'Moderator',
                check: async (message: Message) => {
                    try {
                        const guildSettings = settings.guildSettings(message.guild);
                        const roles = await message.guild.roles.fetch();
                        const role = roles.cache.array().find((r: Role) => r.name.toLowerCase() === guildSettings.modRole.toLowerCase());
                        return role && message.member.roles.cache.get(role.id) !== null;
                    } catch (e) {
                        return false;
                    }
                },
            },
            {
                level: PermLevel.administrator,
                name: 'Administrator',
                check: async (message: Message) => {
                    try {
                        const guildSettings = settings.guildSettings(message.guild);
                        const roles = await message.guild.roles.fetch();
                        const role = roles.cache.array().find((r: Role) => r.name.toLowerCase() === guildSettings.adminRole.toLowerCase());
                        return role && message.member.roles.cache.get(role.id) !== null;
                    } catch (e) {
                        return false;
                    }
                },
            },
            {
                level: PermLevel.owner,
                name: 'Server Owner',
                check: async (message: Message) => {
                    return message.channel.type === 'text' && message.guild.ownerID === message.author.id;
                },
            },
            {
                level: PermLevel.support,
                name: 'Bot Support',
                check: async (message: Message) => {
                    return settings.config.support.includes(message.author.id);
                },
            },
            {
                level: PermLevel.botAdmin,
                name: 'Bot Admin',
                check: async (message: Message) => {
                    return settings.config.admins.includes(message.author.id);
                },
            },
            {
                level: PermLevel.frax,
                name: 'Frax',
                check: async (message: Message) => {
                    return settings.config.owner === message.author.id;
                },
            },
       ];
    }

    nameFor(level: PermLevel) {
        return this.levels.find(l => l.level === level).name;
    }

    async userLevelFrom(message: Message) {
        let effectiveLevel = PermLevel.user;

        const ordered = this.levels.slice(0).sort((a, b) => a.level < b.level ? 1 : -1);

        for (const level of ordered) {
            if (await level.check(message)) {
                effectiveLevel = level.level;
                break;
            }
        }

        return effectiveLevel;
    }
}
