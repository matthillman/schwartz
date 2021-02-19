import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { PermLevel } from '../services/permissions';
import { BaseCommand, CommandCategory, HelpText } from './command';

@injectable()
export class MyLevel extends BaseCommand {
    name = 'myLevel';
    aliases: string[] = ['level'];
    permissionLevel = PermLevel.user;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Tells you your permission level for the current message location',
        usage: 'myLevel',
    };

    async execute(_args: string[], message: Message): Promise<boolean> {
        const level = await this.permissions.userLevelFrom(message);
        const patron = await this.patron.patronLevelFor(message.author);
        await message.reply(`Your permission level is: ${level} - ${PermLevel[level].toTitleCase()},
your patron level is ${patron.userLevel} - ${PatronLevel[patron.userLevel].toTitleCase()},
your guild patron level is ${patron.guildLevel} - ${PatronLevel[patron.guildLevel].toTitleCase()}`);

        return true;
    }


}
