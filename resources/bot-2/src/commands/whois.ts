import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { PatronLevel } from '../services/patron';
import { BaseCommand, CommandCategory, HelpText  } from './command';

@injectable()
export class Whois extends BaseCommand {
    name = 'whois';
    aliases: string[] = ['whoami'];
    patronLevel = PatronLevel.plaid;
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'See all of the registered ally codes',
        usage: 'whois [discord mention]',
    };

    async execute(args: string[], message: Message): Promise<boolean> {
        const mention = args[0];
        const user = (!mention || mention === 'me') ? message.author : await this.getUserFromMention(mention);
        if (!user) {
            await message.react('🤔');
            await message.reply(`Somehow did not find a user from that message?`);
            return true;
        }

        await message.react('⏳');
        const response = await this.api.get(`whois/${user.id}`);
        const data = response.body;

        const hasThisServerExplicitly = message.guild && !!data.find(d => [message.channel.id, message.guild.id].includes(d.server_id));

        await message.react('🎉');

        const fields = [];

        for (const code of data) {
            const decoration = ((message.guild && code.server_id === message.guild.id) || (code.server_id === null && !hasThisServerExplicitly)) ? `\n🏵 Used here` : '';

            fields.push({
                name: `Entry ${data.indexOf(code) + 1}`,
                value: `Ally Code ${code.ally_code}\nServer: ${code.server_id || 'Default'}${decoration}`,
            });
        }

        await message.channel.send({embed: {
            title: `Known ally codes for @${user.username}#${user.discriminator}`,
            fields,
        }});
        return true;
    }
}
