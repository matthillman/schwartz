import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class Mods extends SnapshotCommand {
    name = 'mods';
    aliases: string[] = [];
    patronLevel = PatronLevel.plaid;
    userPatronLevel = PatronLevel.ridiculous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: `Shows mods for a user's character`,
        usage: 'mods [ally code/mention/nothing] [unit]',
    };

    wantsEmbed = true;
    wantsGuild = false;
    argsHaveSearch = true;

    async execute(args: string[], message: Message) {
        console.log(`Starting mods for args [${args.join(', ')}]`);

        await message.react('⏳');
        const parsedArgs = this.parseSearchFromArgs(args);
        for (const code of parsedArgs.code) {
            try {
                await this.doSearch(parsedArgs.search, `member-unit-search/${code}?search=`, async char => {
                    await this.snapReplyForAllyCodes(message, char.id, `member/mods`);
                });
            } catch (err) {
                console.error(`Category search failed with status ${err.message} [${code}] (${parsedArgs.search.join(' ')})`);
                await message.react('🥃');
                await message.reply(`Error finding **${parsedArgs.search.join(' ')}** for **${code}**, please check that this search returns a valid, unlocked unit`);
            }
        }

        return true;
    }

}
