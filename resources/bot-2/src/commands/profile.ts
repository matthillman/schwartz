import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class Profile extends SnapshotCommand {
    name = 'profile';
    aliases: string[] = ['pr'];
    patronLevel = PatronLevel.plaid;
    userPatronLevel = PatronLevel.ridiculous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Shows a profile for the user',
        usage: 'profile [ally code/mention/"me"]',
    };

    wantsEmbed = true;
    wantsGuild = false;

    async execute(args: string[], message: Message) {
        console.log(`Starting profile for ally codes [${args.join(', ')}]`);

        await message.react('⏳');

        await this.snapReplyForAllyCodes(message, args, `member`);

        return true;
    }
}
