import { Client, Message, Channel, GuildChannel, TextChannel } from 'discord.js';
import Enmap from 'enmap';
import { injectable, inject } from 'inversify';
import { TYPES } from '../ioc/types';
import { awaitReply } from '../services/message-responder';
import { Permissions, PermLevel } from '../services/permissions';

@injectable()
export abstract class ScheduleCommand {
    abstract name: string;
    abstract aliases: string[];

    protected needsAdmin = false;

    constructor(
        @inject(TYPES.ScheduleDB) protected scheduleDB: Enmap,
        @inject(TYPES.Permissions) protected permissions: Permissions,
        @inject(TYPES.Client) protected client: Client,
    ) {  }

    async execute(args: string[], message: Message): Promise<boolean> {
        const command = args.shift().toLowerCase();
        if (![this.name, ...this.aliases].map(n => n.toLowerCase()).includes(command)) { return false; }

        if (this.needsAdmin) {
            const userLevel = await this.permissions.userLevelFrom(message);
            if (userLevel < PermLevel.moderator) {
                await message.reply(`🚨 You don't have permission to do this, you need a bot moderator`);
                return true;
            }
        }

        return this.run(command, args, message);

    }
    abstract run(command: string, args: string[], message: Message): Promise<boolean>;

    protected getSchedule(channel: Channel) {
        this.scheduleDB.ensure(channel.id, {});

        return this.scheduleDB.fetch(channel.id);
    }
}

@injectable()
export class ScheduleAdd extends ScheduleCommand {
    name = 'add';
    aliases: string[] = ['replace'];

    needsAdmin = true;

    async run(_command: string, args: string[], message: Message): Promise<boolean> {
        const scheduleKey = args.shift().toLowerCase();

        if (!scheduleKey) {
            await message.reply(`Please supply an event key to set.`);
            return false;
        }

        if (args.length < 2) {
            await message.reply(`Please specify both a channel and a command to run.`);
            return false;
        }

        const channelName = args.shift().replace(/^#/g, '');
        let channel: GuildChannel;

        if (channelName.startsWith('<#')) {
            channel = message.guild.channels.resolve(channelName.replace(/^<#/, '').replace(/>$/, ''));
        } else {
            channel = message.guild.channels.cache.find(c => c.name === channelName);
        }

        if (!channel) {
            await message.reply(`Can't find channel named "${channelName}"`);
        }

        this.scheduleDB.ensure(message.channel.id, {});

        this.scheduleDB.set(message.channel.id, {
            channel: channel.id,
            command: args.join(' '),
        }, scheduleKey);

        await message.reply(`Event ${scheduleKey} added. Remember, you can only schedule this event from this channel`);

        return true;
    }

}

@injectable()
export class ScheduleList extends ScheduleCommand {
    name = 'list';
    aliases: string[] = ['help'];

    async run(_command: string, _args: string[], message: Message): Promise<boolean> {
        const channelSchedules = this.getSchedule(message.channel);

        if (!channelSchedules) {
            await message.reply(`There are no events configured in this channel`);
            return true;
        }

        const channel = message.channel as GuildChannel;

        const configuredEvents = Object.keys(channelSchedules).sort();
        let output = `= Events that can be called in ${channel.name} =\n`;
        configuredEvents.forEach(event => {
            output += `\u200b\n  * ${event}`;
        });
        await message.channel.send(output, {code: 'asciidoc', split: { char: `\u200b` }});
        return true;
    }

}

@injectable()
export class ScheduleRemove extends ScheduleCommand {
    name = 'remove';
    aliases: string[] = ['delete'];

    needsAdmin = true;

    async run(_command: string, args: string[], message: Message): Promise<boolean> {
        const scheduleKey = args.shift().toLowerCase();

        if (!scheduleKey) {
            await message.reply(`Please supply an event key to set.`);
            return false;
        }

        if (!this.scheduleDB.has(message.channel.id, scheduleKey)) {
            await message.reply(`There is no event in this channel for ${scheduleKey}`);
            return false;
        }

        const response = await awaitReply(message, `Are you sure you want to remove the event "${scheduleKey}"?`);

        if (['y', 'yes'].includes(response.toLowerCase())) {
            this.scheduleDB.delete(message.channel.id, scheduleKey);
            await message.reply(`${scheduleKey} was successfully deleted.`);
        } else if (['n','no','cancel'].includes(response)) {
            await message.reply('I did nothing.');
        }

        return true;
    }

}

@injectable()
export class ScheduleExecute extends ScheduleCommand {
    name = 'execute';
    aliases: string[] = [];

    async run(_command: string, args: string[], message: Message): Promise<boolean> {
        const scheduleKey = args.shift().toLowerCase();

        if (!this.scheduleDB.has(message.channel.id, scheduleKey)) {
            await message.reply(`There is no event named "${scheduleKey} configured in this channel`);
        }

        const event = this.scheduleDB.get(message.channel.id, scheduleKey);
        const channel = this.client.channels.resolve(event.channel) as TextChannel;

        if (!channel) {
            await message.reply(`Can't find a channel "${event.channel}"`);
            return false;
        }

        const extraData = args.join(' ');
        await channel.send(`${event.command} ${extraData}`);
        await message.reply(`${scheduleKey} scheduled for ${extraData}`);
        return true;
    }

}

