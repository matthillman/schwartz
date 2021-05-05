import { Client, GuildMember } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { BroadcastProvider } from '../ioc/inversify.config';
import { Broadcast } from '../services/broadcast';
import { API } from '../services/api';


@injectable()
export class BotCommandHandler {
    private _broadcast: Broadcast;
    constructor(
        @inject(TYPES.Client) private client: Client,
        @inject(TYPES.BroadcastProvider) private broadcastProvider: BroadcastProvider,
        @inject(TYPES.Api) protected api: API,
    ) { }

    async init() {
        console.log(`[BOT COMMAND] Starting listener`);
        const broadcast = await this.broadcast();
        broadcast.subscribeBotCommand(async data => {
            switch (data.command) {
                case 'guild-query': {
                    console.log(`Parsing guild-query ${JSON.stringify(data)}`);
                    let guilds = data.guilds;

                    if (!Array.isArray(guilds)) {
                        guilds = [guilds];
                    }

                    const response = [];
                    let role = null;
                    for (const query of guilds) {
                        const guild = this.client.guilds.resolve(query.guild);

                        if (query.role) {
                            const roleExp = new RegExp(query.role, 'i');
                            role = await guild.roles.cache.find(r => roleExp.test(r.name));
                        }

                        await guild.members.fetch();
                        let members;
                        if (query.member) {
                            members = query.member;
                            if (!Array.isArray(members)) {
                                members = [members];
                            }
                            members = members.map(m => guild.members.resolve(m));
                        } else {
                            members = [...guild.members.cache.values()];
                        }
                        for (const member of members as GuildMember[]) {
                            if (!role || role && member.roles.cache.has(role.id)) {
                                response.push({
                                    id: member.id,
                                    guild: guild.id,
                                    username: member.user.username,
                                    discriminator: member.user.discriminator,
                                    email: '',
                                    avatar: member.user.avatar,
                                    roles: [...member.roles.cache.map(r => ({ id: r.id, name: r.name }))],
                                });
                            }
                        }
                    }

                    try {
                        const status = await this.api.post(`guild-query-response`, { response, role });
                        console.log(`Posted guild query response: ${JSON.stringify(status.body)}`);
                    } catch (e) {
                        console.error(e);
                    }
                    break;
                }
                // case 'send-dms': {
                //     console.log(`Parsing send-dms ${JSON.stringify(data)}`);
                //     const start = Date.now();
                //     const URL = data.url;
                //     for (const member of data.members) {
                //         let success = false;
                //         try {
                //             await snapDM(member.ally_code, URL, client.users.get(member.id), client, '', true, data.message);
                //             success = true;
                //             console.log(`DM sent to ${member.ally_code} (${member.id}) for URL [${URL}] (${((new Date).getTime() - start) / 1000} seconds)`);
                //         } catch (error) {
                //             console.error(`DM failed to sent to ${member.ally_code} (${member.id}) for URL [${URL}] [${error}]`);
                //         }

                //         const status = await this.api.post(`send-dm-response`, { member: member.id, success, context: data.context });
                //         console.log(`Posted dm query response: ${status}`);
                //     }
                //     console.log(`⏲  Sending ${data.members.length} DMs took ${((new Date).getTime() - start) / 1000} seconds`);

                //     break;
                // }
                default:
                    console.error(`Unknown command ${data.command}`);
            }
        });
        console.log(`[BOT COMMAND] Listening`);
    }

    private async broadcast() {
        if (this._broadcast) { return this._broadcast; }
        this._broadcast = await this.broadcastProvider();
        return this._broadcast;
    }
}
