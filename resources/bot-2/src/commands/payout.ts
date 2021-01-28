import { Message, MessageEmbedOptions, TextChannel } from 'discord.js';
import Enmap from 'enmap';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { PermLevel } from '../services/permissions';
import { rgbToHex, hslToRgb } from '../util/color';
import { BaseCommand, CommandCategory, HelpText } from './command';
import got from 'got';

const EXAMPLE = [
    {
        name: 'Frax',
        flag: 'flag_us',
        ally_code: '552325555',
        payout: '21:00',
    },
];

interface PayoutMember {
    name: string;
    payout: string;
    flag?: string;
    ally_code?: string;

    // Internal fields
    payout_hours?: number;
    payout_minutes?: number;
    timeUntilPayout?: number;
    time?: string;
};

@injectable()
export class Payout extends BaseCommand {
    name = 'payout';
    aliases: string[] = ['payouts'];
    permissionLevel = PermLevel.moderator;
    help: HelpText = {
        category: CommandCategory.misc,
        description: 'Functions to show the payout data',
        usage: 'payout [start|stop|register|get]',
    };

    @inject(TYPES.PayoutDB) private payoutDB: Enmap;
    @inject(TYPES.ApiHost) protected baseURL: string;

    async init() {
        await this.payoutDB.defer;
        this.client.on('ready', () => {
            this.payoutDB.forEach((_, key) => {
                if (this.payoutDB.get(key, 'running')) {
                    console.debug(`Starting payout timer in ${key}`);
                    this.updatePayouts(`${key}`);
                }
            })
        });
    }

    async execute(args: string[], message: Message) {
        const command = args[0];
        const possibleCommand = (command || '').toLowerCase();

        if (possibleCommand === 'register') {
            const newChannel = !this.payoutDB.has(message.channel.id);
            this.payoutDB.ensure(message.channel.id, {});

            if (message.attachments.size === 0) {
                await message.channel.send('🛑 You must include a payout.json when registering');
                return;
            }
            const url = message.attachments.first().url;
            const response = await got<PayoutMember[]>(url, { responseType: 'json' });

            try {
                const data = this.parseData(response.body);
                this.payoutDB.set(message.channel.id, data, 'data');
                this.payoutDB.set(message.channel.id, true, 'running');

                console.log('Payout register updated');

                if (newChannel) {
                    console.log('New payout, starting timer');
                    this.updatePayouts(message.channel.id);
                }

                await message.delete();
            } catch (error) {
                await message.channel.send(error.message);
                return;
            }
        } else if (['get', 'current', 'example'].includes(possibleCommand)) {
            const channelPayouts = this.payoutDB.ensure(message.channel.id, {});
            const data = channelPayouts.data || EXAMPLE;
            await message.channel.send({
                files: [{
                    attachment: Buffer.from(JSON.stringify(data)),
                    name: 'current_payouts.json',
                }],
            });
            return;
        } else if (possibleCommand === 'stop') {
            this.payoutDB.ensure(message.channel.id, {});
            this.payoutDB.set(message.channel.id, false, 'running');
            await message.delete();
        } else if (possibleCommand === 'start') {
            this.payoutDB.ensure(message.channel.id, {});
            this.payoutDB.set(message.channel.id, true, 'running');
            this.updatePayouts(message.channel.id);
            await message.delete();
        }

        return true;
    }

    parseData(data: PayoutMember[]) {
        return data.map(member => {
            const keys = Object.keys(member);

            if (!keys.includes('name') || !keys.includes('payout') || !member.name.length || !member.payout.length) {
                throw new Error(`Each member in the JSON must have both a "name" and "payout" key`);
            }

            const payoutParts = member.payout.split(':');
            member.payout_hours = parseInt(payoutParts[0]);
            member.payout_minutes = parseInt(payoutParts[1]);

            member.flag = `:${(member.flag || 'flag_white').replace(/^:|:$/g, '')}:`;

            return member;
        });
    }

    sortByPayout(players: PayoutMember[]) {
        const now = new Date();

        const sortedPlayers = players.map(player => {
            const payout = new Date();
            payout.setUTCHours(player.payout_hours);
            payout.setUTCMinutes(player.payout_minutes);
            if (payout <= now) { payout.setDate(payout.getDate() + 1); }
            player.timeUntilPayout = payout.getTime() - now.getTime();

            const diff = new Date(player.timeUntilPayout);
            const round = diff.getTime() % 60000;
            diff.setTime(diff.getTime()- round + (round < 30000 ? 0 : 60000));

            player.time = `${String(diff.getUTCHours()).padStart(2, '00')}:${String(diff.getUTCMinutes()).padStart(2, '00')}`;

            return player;
        });
        sortedPlayers.sort((a, b) => {
            const diff = a.timeUntilPayout - b.timeUntilPayout;
            return diff === 0 ? a.name.localeCompare(b.name) : diff;
        });

        const playersByTime = new Map<string, PayoutMember[]>();

        sortedPlayers.forEach(player => {
            if (!playersByTime.has(player.time)) {
                playersByTime.set(player.time, []);
            }
            playersByTime.get(player.time).push(player);
        });

        return playersByTime;
    }

    generateEmbed(players: PayoutMember[]): MessageEmbedOptions {
        const sortedPlayers = this.sortByPayout(players);
        const now = new Date();
        const hue = Math.abs((now.getHours() % 2) - (now.getMinutes() / 60));
        const color = parseInt(rgbToHex.apply(null, hslToRgb(hue, 1, 0.5)).substr(1), 16);
        const desc = '**Time until next payout**:';
        const fields = [];

        const linkify = player => player.ally_code ? `[${player.name}](${this.baseURL}/member/${player.ally_code}/)` : player.name;

        sortedPlayers.forEach((payoutPlayers, payout) => {
            const name = payout;
            let value = '';
            payoutPlayers.forEach(player => {
                value += `${player.flag} ${linkify(player)}\n`;
            });
            fields.push({name, value, inline: true});
        });

        return {
            description: desc,
            color,
            fields,
            timestamp: now,
            footer: {
                text: 'May the Schwartz be with you',
                icon_url: 'https://schwartz.hillman.me/images/schwartz.jpg',
            },
        };
    }

    async updatePayouts(key: string) {
        const channelData = this.payoutDB.get(key);
        const data = this.parseData(channelData.data);
        if (channelData.running) {
            const channel = this.client.channels.resolve(key) as TextChannel;
            let sent = false;
            const embed = this.generateEmbed(data);
            if (this.payoutDB.has(key, 'message')) {
                try {
                    const message = await channel.messages.fetch(channelData.message);
                    message.edit({ embed });
                    sent = true;
                } catch (e) {
                    console.error(`Error fetching payout message ${channelData.message}, sending new message`);
                }
            }
            if (!sent) {
                const message = await channel.send({ embed });
                this.payoutDB.set(key, message.id, 'message');
            }

            setTimeout(() => this.updatePayouts(key), 60000 - Date.now() % 60000);
        }
    }
}
