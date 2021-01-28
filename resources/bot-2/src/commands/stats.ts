import { Message, version } from 'discord.js';
import { injectable } from 'inversify';
import { BaseCommand, CommandCategory, HelpText } from './command';

@injectable()
export class Stats extends BaseCommand {
    name = 'stats';
    aliases: string[] = [];
    help: HelpText = {
        category: CommandCategory.misc,
        description: 'Gives some useful bot statistics',
        usage: 'stats',
    };

    async execute(_args: string[], message: Message): Promise<boolean> {
        const days = Math.floor(this.client.uptime / (1000 * 60 * 60 * 24));
        let next = this.client.uptime - (days * 1000 * 60 * 60 * 24);
        const hours = Math.floor(next / (1000 * 60 * 60));
        next = next - (days * 1000 * 60 * 60 );
        const mins = Math.floor(next / (1000 * 60));
        next = next - (mins * 1000 * 60);
        const secs = Math.floor(next / 1000);
        const duration = ` ${days} [days], ${hours} [hrs], ${mins} [mins], ${secs} [secs]`;
        message.channel.send(`= STATISTICS =
• Mem Usage  :: ${(process.memoryUsage().heapUsed / 1024 / 1024).toFixed(2)} MB
• Uptime     :: ${duration}
• Users      :: ${this.client.users.cache.size.toLocaleString()}
• Servers    :: ${this.client.guilds.cache.size.toLocaleString()}
• Channels   :: ${this.client.channels.cache.size.toLocaleString()}
• Discord.js :: v${version}
• Node       :: ${process.version}`, {code: 'asciidoc'});

        return true;
    }


}
