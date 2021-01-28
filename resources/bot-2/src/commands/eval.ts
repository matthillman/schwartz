import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PermLevel } from '../services/permissions';
import { BaseCommand, CommandCategory, HelpText } from './command';

const TICKS = '```';

@injectable()
export class Eval extends BaseCommand {
    name = 'eval';
    aliases: string[] = [];
    permissionLevel = PermLevel.owner;
    help: HelpText = {
        category: CommandCategory.system,
        description: 'Evaluates arbitrary javascript',
        usage: 'eval [...code]',
    };

    async execute(args: string[], message: Message): Promise<boolean> {
        const code = args.join(' ');

        console.debug(`[EVAL] eval(${code})`);

        try {
            // tslint:disable-next-line: no-eval
            const evaled = eval(code);
            const cleaned = await this.clean(evaled);
            message.channel.send(`${TICKS}js\n${cleaned}\n${TICKS}`);
        } catch (err) {
            message.channel.send(`\`ERROR\`\n${TICKS}x1\n${await this.clean(err)}\n${TICKS}`);
        }

        return true;
    }


}
