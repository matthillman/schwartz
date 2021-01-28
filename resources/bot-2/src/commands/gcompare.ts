import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class GCompare extends SnapshotCommand {
    name = 'gcompare';
    aliases: string[] = ['gc'];
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Compares two guilds',
        usage: 'gcompare [guild id or ally code] [guild id or ally code]',
    };

    wantsEmbed = true;
    wantsGuild = true;

    async execute(args: string[], message: Message) {
        console.log(`Starting compare for guilds [${args.join(', ')}]`);

        await message.react('⏳');

        await this.snapReplyForGuilds(message, args, `compare`);

        return true;
    }

}
