import { inject, injectable } from 'inversify';
import { default as Redis } from 'ioredis';
import { TYPES } from '../ioc/types';


@injectable()
export class Broadcast {
    private guildSubscribers: Broadcast.Subscriber[] = []
    private userSubscribers: Broadcast.Subscriber[] = []
    private botSubscribers: Broadcast.Subscriber[] = []

    private readonly redis: Redis.Redis;

    private subscriptionSetup = false;

    constructor(
        @inject(TYPES.RedisClient) config: Broadcast.RedisConfig,
    ) {
        this.redis = new Redis(config);
    }

    subscribeGuild(id: string, callback: () => void) {
        this.guildSubscribers.push({id, callback});
    }

    subscribeUser(id: string, callback: () => void) {
        this.userSubscribers.push({id, callback});
    }

    subscribeBotCommand(callback: (data: any) => void) {
        this.botSubscribers.push({id: `${Date.now()}`, callback});
    }

    async subscribe() {
        if (this.subscriptionSetup) {
            return;
        }
        this.redis.on('pmessage', (_subscribed, channel, message) => {
            try {
                message = JSON.parse(message);
                console.debug(`Channel: ${channel} -> Event: ${message.event}`);

                if (channel === 'private-guilds' && message.event === 'guild.fetched') {
                    this.guildSubscribers.forEach((listener, index) => {
                        // tslint:disable-next-line: triple-equals
                        if (listener.id == message.data.guild.guild_id) {
                            this.guildSubscribers.splice(index, 1);
                            listener.callback();
                        }
                    })
                } else if (message.event === 'mods.fetched') {
                    this.userSubscribers.forEach((listener, index) => {
                        if (listener.id === message.data.mods) {
                            this.userSubscribers.splice(index, 1);
                            listener.callback();
                        }
                    });
                } else if (message.event === 'bot.command') {
                    this.botSubscribers.forEach(listener => {
                        listener.callback(message.data);
                    });
                }
            } catch (err) {
                console.error(`No JSON Message [${err.message}]`);
            }
        });

        await this.redis.psubscribe('*').catch(err => console.error(`Redis could not subscribe [${err.message}]`));

        this.subscriptionSetup = true;
    }
}

// tslint:disable-next-line: no-namespace
export namespace Broadcast {
    export interface Subscriber {
        id: string,
        callback: (data?: any) => void,
    };

    export interface RedisConfig {
        host?: string;
        port?: number;
    };
};
