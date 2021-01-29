import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

@injectable()
export class UnitSearch extends SnapshotCommand {
    name = 'unitSearch';
    aliases: string[] = ['us', 'units'];
    patronLevel = PatronLevel.plaid;
    userPatronLevel = PatronLevel.ridiculous;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Shows all unit search results for a given query',
        usage: 'unitSearch [search string]',
    };

    wantsEmbed = true;
    wantsGuild = false;

    async execute(args: string[], message: Message) {
        console.log(`Starting unit search for [${args.join(', ')}]`);

        await message.react('⏳');

        try {
            await this.doSearch(args, `unit-search?search=`, async units => {

                let output = `= Units Search Results =\n`;

                for (const unit of units.data) {
                    output += `\u200b
ID: ${unit.base_id}
NAME: ${unit.name}
DESCRIPTION: ${unit.description}\n`;
                }

                if (units.next_page_url) {
                    output += `
                    First ${units.per_page} of ${units.total} matching units; use a more specific search to narrow the results\n`;
                }

                await message.channel.send(output, {
                    code: 'asciidoc',
                    split: {
                        char: `\u200b`,
                    },
                });
            });
        } catch (err) {
            console.error(`Unit search failed with status ${err.message} (${args.join(' ')})`);
            await message.react('🥃');
            await message.reply(`Error finding a unit with the search string**${args.join(' ')}**, please check that this is really a unit`);
        }

        return true;
    }

}
