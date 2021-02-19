import { Message } from 'discord.js';
import { inject, injectable } from 'inversify';
import { inspect } from 'util';
import { TYPES } from '../ioc/types';
import { awaitReply } from '../services/message-responder';
import { PermLevel } from '../services/permissions';
import { Settings } from '../services/settings';
import { BaseCommand, CommandCategory, HelpText } from './command';

@injectable()
export class Configuration extends BaseCommand {
    name = 'set';
    aliases: string[] = ['setting', 'settings', 'conf'];
    permissionLevel = PermLevel.moderator;
    help: HelpText = {
        category: CommandCategory.system,
        description: 'View or change settings for your server.',
        usage: 'set [view/get/edit] [key] [value]',
    };

    constructor(
        @inject(TYPES.Settings) protected settings: Settings
    ) {
        super();
    }

    async execute(args: string[], message: Message): Promise<boolean> {
        const action = (args.shift() ?? '').toLowerCase();
        const key = (args.shift() ?? '').toLowerCase();
        const value = args.join(' ');

        const settings = this.settings.guildSettings(message.guild);
        const overrides = this.settings.getSettings<any>(message.guild.id, {});

        if (action === 'edit') {
            if (!key) {
                await message.reply('Please specify a key to edit');
                return true;
            }
            if (!settings[key]) {
                await message.reply('This key does not exist in the settings');
                return true;
            }
            if (value.length < 1) {
                await message.reply('Please specify a new value');
                return true;
            }
            if (value === settings[key]) {
                await message.reply('This setting already has that value!');
            }

            if (!this.settings.has(message.guild.id)) {
                this.settings.set(message.guild.id, {});
            }

            this.settings.set(message.guild.id, value, key);

            message.reply(`${key} successfully edited to "${value}"`);
        } else if (action === 'reset') {
            if (!key) {
                await message.reply('Please specify a key to reset.');
                return true;
            }
            if (!settings[key]) {
                await message.reply('This key does not exist in the settings');
                return
            }
            if (!overrides[key]) {
                await message.reply('This key does not have an override and is already using defaults.');
                return true;
            }

            const response = await awaitReply(
                message,
                `Are you sure you want to reset ${key} to the default value?`
            );

            if (['y', 'yes'].includes(response.toLowerCase())) {
                delete overrides[key];
                this.settings.set(message.guild.id, overrides);
                message.reply(`${key} was successfully reset.`);
            } else if (['n', 'no', 'cancel'].includes(response)) {
                message.reply('Action cancelled.');
            }
        } else if (action === 'get') {
            if (!key) {
                await message.reply('Please specify a key to view');
                return true;
            }
            if (!settings[key]) {
                await message.reply('This key does not exist in the settings');
                return true;
            }
            const isDefault = !overrides[key] ? '\nThis is the default global default value.' : '';
            await message.reply(`The value of ${key} is currently ${settings[key]}${isDefault}`);
        } else {
            await message.channel.send(inspect(settings), { code: 'json' });
        }

        return false;
    }
}
