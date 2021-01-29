import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class Compare extends SnapshotCommand {
    name = 'compare';
    aliases: string[] = ['c'];
    patronLevel = PatronLevel.plaid;
    userPatronLevel = PatronLevel.ridiculous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Compares ally codes',
        usage: 'compare [ally code]{1, }',
    };

    wantsEmbed = true;
    wantsGuild = false;

    async execute(args: string[], message: Message) {
        console.log(`Starting compare for ally codes [${args.join(', ')}]`);

        await message.react('⏳');

        await this.snapReplayForCompare(message, args, `member/compare`, `members`);

        return true;
    }

}
