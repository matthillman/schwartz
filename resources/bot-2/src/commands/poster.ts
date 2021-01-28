import { Message, MessageAttachment } from 'discord.js';
import { injectable } from 'inversify';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class Poster extends SnapshotCommand {
    name = 'poster';
    aliases: string[] = [];
    help: HelpText = {
        category: CommandCategory.misc,
        description: 'Shows the Schwartz recruiting poster',
        usage: 'poster',
    };

    wantsEmbed = false;
    wantsGuild = false;

    async execute(args: string[], message: Message) {
        console.log(`Starting poster fetch`);

        await message.react('⏳');

        const URL = `${this.api.baseURL}poster`;
        try {
            const buffer = await this.snapshot(URL);
            await message.channel.send(new MessageAttachment(buffer, `TheSchwartzies.png`));
        } catch (err) {
            await message.reply('🤔 Something really bad happened.');
        }

        return true;
    }
}
