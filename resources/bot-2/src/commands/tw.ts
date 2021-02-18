import { Message } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { PermLevel } from '../services/permissions';
import { BaseCommand, CommandCategory, HelpText  } from './command';
import { GCompare } from './gcompare';

@injectable()
export class TW extends BaseCommand {
    name = 'tw';
    aliases: string[] = [];
    permissionLevel = PermLevel.user;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'See **gcompare**',
        usage: 'use **gc**',
    };

    @inject(TYPES.GCompare) private gcompare: GCompare;

    async execute(args: string[], message: Message): Promise<boolean> {
        await message.reply(`FYI, \`tw compare\` has been renamed to \`${this.gcompare.name}\` (aka \`${this.gcompare.aliases.join('`, `')}\`)`);
        if (args[0] === 'compare') {
            args.shift(); // remove 'compare'
        }
        return this.gcompare.handle([this.gcompare.name, ...args].join(' '), message);
    }
}
