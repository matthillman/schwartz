import { Message } from 'discord.js';
import { injectable } from 'inversify';
import { BaseCommand, CommandCategory, HelpText  } from './command';

@injectable()
export class Ping extends BaseCommand {
    name = 'ping';
    aliases: string[] = [];
    help: HelpText = {
        category: CommandCategory.swgoh,
        description: 'It... like... pings. Then Pongs. And it"s not Ping Pong.',
        usage: 'ping',
    };

    async execute(_args: string[], message: Message): Promise<boolean> {
        const msg = await message.channel.send(`Pong`);
        msg.edit(`Pong!
Latency is ${msg.createdTimestamp - message.createdTimestamp}ms. API Latency is ${Math.round(this.client.ws.ping)}ms.`);
        return true;
    }
}
