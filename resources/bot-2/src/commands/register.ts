import { Message } from 'discord.js';
import { HTTPAlias } from 'got/dist/source';
import { injectable } from 'inversify';
import { PermLevel } from '../services/permissions';
import { BaseCommand, CommandCategory, HelpText  } from './command';

@injectable()
export class Register extends BaseCommand {
    name = 'register';
    aliases: string[] = ['reg'];
    permissionLevel = PermLevel.user;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Register an ally code to a discord ID for later convenience',
        usage: 'register [ally code]',
    };

    async execute([command, allyCode, ..._args]: string[], message: Message): Promise<boolean> {
        let action: HTTPAlias
        if (!allyCode) {
            allyCode = command;
            action = 'post';
        } else {
            command = command.toLowerCase();

            if (command === '-d' || command === '--delete') {
                action = 'delete';
            } else {
                await message.react('🤔');
                await message.reply(`Invalid arguments`);
                return true;
            }
        }

        if (allyCode) {
            allyCode = allyCode.replace(/\-/g, '');
        }

        let realAllyCode = null;
        if (/^[0-9]{9}$/.test(allyCode)) {
            realAllyCode = allyCode;
        }

        if (realAllyCode === null) {
            await message.react('🤔');
            message.reply(`${allyCode} does not appear to be a valid ally code`);
            return true;
        } else {
            await message.react('⏳');
            const user = message.author;
            await this.api.execute(action, `registration/${realAllyCode}/${user.id}/${message.guild.id}`);
            console.log(`Registered ally code ${JSON.stringify(realAllyCode)} for user ${user.id}`);
        }
        return true;
    }
}
