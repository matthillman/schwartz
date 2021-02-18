import { Message } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { CommandList } from '../services/command-list';
import { Settings } from '../services/settings';
import { BaseCommand, CommandCategory, HelpText  } from './command';

@injectable()
export class Help extends BaseCommand {
    name = 'help';
    aliases: string[] = ['?', 'h'];
    help: HelpText = {
        category: CommandCategory.system,
        description: 'Displays all the available commands for your permission level',
        usage: 'help [command?]',
    };

    @inject(TYPES.CommandList) private commands: CommandList;
    @inject(TYPES.Settings) private settings: Settings;

    async execute([commandName, ..._args]: string[], message: Message): Promise<boolean> {
        const userLevel = await this.permissions.userLevelFrom(message);
        const command = commandName ? this.commands.list.find(c => [c.name, ...c.aliases].map(n => n.toLowerCase()).includes(commandName)) : undefined;

        if (!command) {
            const visibleCommands = this.commands.list.filter(cmd => cmd.permissionLevel <= userLevel && (!!message.guild || !cmd.guildOnly))
            const longestNameLength = visibleCommands.reduce((longest, cmd) => Math.max(longest, cmd.name.length), 0);
            const settings = this.settings.guildSettings(message.guild);
            let currentCategory = '';
            let output = `= Command List =\n\n[Use ${settings.prefix}help [command] for details]\n`;
            const sorted = visibleCommands.sort((a, b) => a.help.category > b.help.category ? 1 : ( a.name > b.name && a.help.category === b.help.category ? 1 : -1 ))

            for (const cmd of sorted) {
                const category = cmd.help.category;
                if (currentCategory !== category) {
                    output += `\u200b\n== ${category} ==\n`;
                    currentCategory = category;
                }
                output += `${settings.prefix}${cmd.name}${' '.repeat(longestNameLength - cmd.name.length)} :: ${cmd.help.description}\n`;
            }
            await message.channel.send(output, {code: `asciidoc`, split: { char: `\u200b` }});
        } else {
            if (command && command.permissionLevel <= userLevel) {
            await message.channel.send(`= ${command.name} = \n${command.help.description}\nusage::\n${command.help.usage}\naliases:: ${command.aliases.join(', ')}\n= /${command.name} =`, { code: `asciidoc` });
            } else {
                await message.reply(`Can't find a command "${commandName}"`);
            }
        }
        return true;
    }
}
