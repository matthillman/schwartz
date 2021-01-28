import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { BaseCommand, CommandCategory, HelpText } from './command';

@injectable()
export class MyLevel extends BaseCommand {
    name = 'myLevel';
    aliases: string[] = ['level'];
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'Tells you your permission level for the current message location',
        usage: 'myLevel',
    };

    async execute(_args: string[], message: Message): Promise<boolean> {
        const level = await this.permissions.userLevelFrom(message);
        await message.reply(`Your permission level is: ${level} - ${this.permissions.nameFor(level)}`);

        return true;
    }


}
