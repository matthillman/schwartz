import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class Faction extends SnapshotCommand {
    name = 'faction';
    aliases: string[] = ['f'];
    patronLevel = PatronLevel.plaid;
    userPatronLevel = PatronLevel.ridiculous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: `Shows a user's characters from the given faction`,
        usage: 'faction [ally code/mention/nothing] [faction]',
    };

    wantsEmbed = true;
    wantsGuild = false;
    argsHaveSearch = true;

    async execute(args: string[], message: Message) {
        console.log(`Starting faction for args [${args.join(', ')}]`);

        await message.react('⏳');
        const parsedArgs = this.parseSearchFromArgs(args);

        try {
            await this.doSearch(parsedArgs.search, `category-search?search=`, async (category) => {
                for (const code of parsedArgs.code) {
                    await this.snapReplayForCompare(message, category.data[0].category_id, `member/${code}/characters`, `category`);
                }
            });
        } catch (err) {
            console.error(`Category search failed with status ${err.message} [${parsedArgs.code.join(', ')}] (${parsedArgs.search.join(' ')})`);
            await message.react('🥃');
            await message.reply(`Error finding **${parsedArgs.search.join(' ')}** for **${parsedArgs.code.join(', ')}**, please check that this search returns a valid, unlocked unit`);
        }

        return true;
    }

}
