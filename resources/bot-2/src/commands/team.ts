import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { CommandCategory, HelpText, SnapshotCommand } from './command';

const teamList = [
    {label: 'General Skywalker', value: 'gs'},
    {label: 'LS Geo TB', value: 'lsgeo'},
    {label: 'Geo TB', value: 'geo'},
    {label: 'TW', value: 'tw'},
    {label: 'Legendaries', value: 'legendary'},
    {label: 'Darth Malak', value: 'malak'},
    {label: 'Hoth TB', value: 'tb'},
    {label: 'STR', value: 'str'},
    {label: 'CT Pit', value: 'pit'},
];


@injectable()
export class Team extends SnapshotCommand {
    name = 'team';
    aliases: string[] = ['t'];
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Shows teams for a user',
        usage: 'team [team key] [ally code]',
    };

    wantsEmbed = true;
    wantsGuild = false;
    argsHaveSearch = true;

    async execute(args: string[], message: Message) {
        console.log(`Starting team for args [${args.join(', ')}]`);

        await message.react('⏳');
        const parsedArgs = this.parseSearchFromArgs(args);

        if (!parsedArgs.search.length) {
            await message.reply(`\`\`\`asciidoc
The following teams are available:
${teamList.reduce((prev, t) => `${prev}${t.value}${' '.repeat(10 - t.value.length)} :: ${t.label}\n`, '')}
\`\`\``);
        }

        let team = parsedArgs.search.join(' ');
        let teamAsInt = parseInt(team);

        if (Number.isNaN(teamAsInt)) { teamAsInt = 0; }

        if (!teamList.map(t => t.value).includes(team) && team !== 'mods' && teamAsInt < 1) {
            await message.reply(`${team} is not a valid team key`);
            return true;
        }

        if (team === 'mods') {
            team = 'tw_mods';
        } else if (team === 'pit') {
            team = '28';
        }

        for (const code of parsedArgs.code) {
            await this.snapReplyForAllyCodes(message, code, `member`, `/${team}`);
        }

        return true;
    }

}
