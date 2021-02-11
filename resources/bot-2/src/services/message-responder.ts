import { Message } from 'discord.js';
import { inject, injectable } from 'inversify';
import { NoPermissionsError, PatronError } from '../commands/command';
import { Profile } from '../commands/profile';
import { TYPES } from '../ioc/types';
import { Settings } from './settings';
import { Recruiting } from '../commands/recruiting';
import { CommandList } from './command-list';
import { Help } from '../commands/help';

export async function awaitReply(message: Message, question: string, limit = 60000) {
    const filter = m => m.author.id === message.author.id;
    await message.channel.send(question);
    try {
        const collected = await message.channel.awaitMessages(filter, { max: 1, time: limit, errors: ['time'] });
        return collected.first().content;
    } catch (e) {
        return '';
    }
}

@injectable()
export class MessageResponder {
    constructor(
        @inject(TYPES.CommandList) private commands: CommandList,
        @inject(TYPES.Recruiting) private recruiting: Recruiting,
        @inject(TYPES.Profile) private profile: Profile,

        @inject(TYPES.Help) private help: Help,

        @inject(TYPES.Settings) private settings: Settings,
    ) {  }

    async handle(message: Message): Promise<boolean> {
        if (message.author.bot) { return Promise.reject(); }
        const settings = this.settings.guildSettings(message.guild);
        await this.recruiting.handleRecruitingWatch(message, this.profile);
        const hasPrefix = message.content.indexOf(settings.prefix) === 0;

        if (message.guild && !hasPrefix) { return Promise.reject(); }

        const content = hasPrefix ? message.content.slice(settings.prefix.length) : message.content;

        for (const command of [...this.commands.list, this.help]) {
            try {
                const handled = await command.handle(content, message);
                if (handled) { return true; }
            } catch (err) {
                if (err instanceof NoPermissionsError) {
                    if (settings.systemNotice) {
                        message.channel.send(err.message);
                    }

                    // We found the right command, just didn't have permissions
                    return true;
                }
                if (err instanceof PatronError) {
                    message.channel.send(err.message);

                    // We found the right command, just didn't have permissions
                    return true;
                }
                console.error(`[MR] ${err}`);
            }
        }

        return Promise.reject();
    }
}
