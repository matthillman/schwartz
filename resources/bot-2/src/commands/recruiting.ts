import { GuildChannel, Message, TextChannel } from 'discord.js';
import Enmap from 'enmap';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { PatronLevel } from '../services/patron';
import { PermLevel } from '../services/permissions';
import { BaseCommand, Command, CommandCategory, HelpText  } from './command';

@injectable()
export class Recruiting extends BaseCommand {
    name = 'recruiting';
    aliases: string[] = [];
    permissionLevel = PermLevel.moderator;
    patronLevel = PatronLevel.plaid;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Echos the profile command in *this* channel for any gg links found in the given channel',
        usage: 'recruiting [channel to watch]',
    };

    constructor(
        @inject(TYPES.RecruitDB) protected recruitDB: Enmap,
    ) {
        super();
    }

    async execute([channelName, ...args]: string[], message: Message): Promise<boolean> {
        const name = channelName.replace(/^#/g, '');

        let channel: GuildChannel;
        if (channelName.startsWith('<#')) {
            channel = message.guild.channels.resolve(channelName.replace(/^<#/, '').replace(/>$/, ''));
        } else {
            channel = message.guild.channels.cache.find(c => c.name === channelName);
        }

        if (!channel) {
            await message.reply(`Can't find a channel named "${name}"`)
            return true;
        };

        this.recruitDB.ensure(message.guild.id, {});

        const recruitingProfileWatcher = {
            channel: channel.id,
            outputChannel: message.channel.id,
            command: args.join(' '),
        };

        this.recruitDB.set(message.guild.id, recruitingProfileWatcher, 'info');

        await message.reply(`Watcher set up.`);
        return true;
    }

    async handleRecruitingWatch(message: Message, command: Command) {
        if (message.guild && this.recruitDB.has(message.guild.id)) {
            const recruitingWatcherSettings = this.recruitDB.get(message.guild.id).info;

            if (message.channel.id === recruitingWatcherSettings.channel) {
                const matches = message.content.match(/swgoh.gg\/p\/([0-9]{9})/);

                if (matches) {
                    const allyCode = matches[1];
                    const outChannel = message.guild.channels.resolve(recruitingWatcherSettings.outputChannel) as TextChannel;

                    console.debug(`[RECRUITING] Firing profile command for recruit ${allyCode} in "#${outChannel.name}"`);
                    const prMessage = await outChannel.send(`Fetching profile for ${allyCode}…`);
                    await command.execute([allyCode], prMessage);
                    await prMessage.react('🎉');
                }
            }
        }
    }
}
