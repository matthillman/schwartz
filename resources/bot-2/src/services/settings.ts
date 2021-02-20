import { Guild } from 'discord.js';
import Enmap from 'enmap';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';

export interface BotSettings {
    prefix: string;
    modLogChannel: string;
    modRole: string;
    adminRole: string;
    systemNotice: string;
    welcomeChannel: string;
    welcomeMessage: string;
    welcomeEnabled: string;
    recruitingChannelTemplate: string;
    recruitingEnabled: string;
    recruitingRecruitRole: string;
    recruitingRecruiterRole: string;
}

export enum Environment {
    local,
    production,
}

export interface BotConfig {
    owner: string;
    admins: string[];
    support: string[];
    env: Environment;
    botGuild: string;
}

const defaultSettings: BotSettings = {
    prefix: '-',
    modLogChannel: 'mod-log',
    modRole: 'Moderator',
    adminRole: 'Administrator',
    systemNotice: 'true', // This gives a notice when a user tries to run a command that they do not have permission to use.
    welcomeChannel: 'welcome',
    welcomeMessage: 'Say hello to {{user}}, everyone! We all need a warm welcome sometimes :D',
    welcomeEnabled: 'false',
    recruitingChannelTemplate: '{{user}}_intro',
    recruitingEnabled: 'false',
    recruitingRecruitRole: 'Recruit',
    recruitingRecruiterRole: 'Recruiter',
}

@injectable()
export class Settings {
    private settingsDB: Enmap;
    readonly prefix: string;
    readonly config: BotConfig;

    constructor(
        @inject(TYPES.SettingsDB) settingsDB: Enmap,
        @inject(TYPES.Prefix) prefix: string,
        @inject(TYPES.Config) config: BotConfig,
    ) {
        this.settingsDB = settingsDB;
        this.prefix = prefix;
        this.config = config;
    }

    guildSettings(guild?: Guild): BotSettings {
        const defaults = { ...defaultSettings, prefix: this.prefix || defaultSettings.prefix };
        if (!guild) { return defaults; }

        this.settingsDB.ensure(guild.id, defaultSettings);

        const overrides = this.settingsDB.fetch(guild.id);

        return { ...defaults, ...overrides };
    }


    getSettings<T>(key: string | number | null, defaults: T): T {
        if (!key) { return defaults; }

        this.settingsDB.ensure(key, defaults);

        return { ...defaults, ...this.settingsDB.fetch(key) };
    }

    get(key: string | number) {
        return this.settingsDB.fetch(key);
    }

    has(key: string | number) {
        return this.settingsDB.has(key);
    }

    set(key: string | number, value: any, path: string = null) {
        this.settingsDB.set(key, value, path);
    }
}
