import { Client, DMChannel, Message, MessageAttachment, NewsChannel, TextChannel, User } from 'discord.js';
import puppeteer from 'puppeteer';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { API } from '../services/api';
import { Permissions, PermLevel } from '../services/permissions';
import { Broadcast } from '../services/broadcast';
import { BroadcastProvider } from '../ioc/inversify.config';
import { inspect } from 'util';
import { CombinedPatronLevel, Patron, PatronLevel } from '../services/patron';

export enum CommandCategory {
    system = 'System',
    swgoh = 'SWGOH',
    util = 'Util',
    misc = 'Miscellaneous',
};

export interface HelpText {
    category: CommandCategory;
    description: string;
    usage: string;
}

export interface Command {
    name: string;
    aliases: string[];
    permissionLevel: PermLevel;
    patronLevel: PatronLevel;
    guildOnly: boolean;
    help: HelpText;

    init?();

    handle(content: string, message: Message): Promise<boolean>;
    execute(args: string[], message: Message): Promise<boolean>;
};

const REACT_LIST = ['🍺', '🍻', '🥂', '🍷', '🥃', '🍸', '🍹', '🧉'];

export class NoPermissionsError extends Error {
    constructor(required: PermLevel, actual: PermLevel) {
        super(`You do not have permission to use this command.
 Your permission level is ${actual} (${PermLevel[actual].toTitleCase()})
 This command requires level ${required} (${PermLevel[required].toTitleCase()})`);

        // restore prototype chain
        const actualProto = new.target.prototype;

        if (Object.setPrototypeOf) { Object.setPrototypeOf(this, actualProto); }
        else { (this as any).__proto__ = actualProto; }
    }
}

export class PatronError extends Error {
    constructor(required: PatronLevel, actual: CombinedPatronLevel) {
        super(`You do not have the patron level to use this command.
 Your patron level is ${actual.userLevel} (${PatronLevel[actual.userLevel].toTitleCase()})
 Your guild's patron level is ${actual.guildLevel} (${PatronLevel[actual.guildLevel].toTitleCase()})
 This command requires level ${required} (${PatronLevel[required].toTitleCase()})`);

        // restore prototype chain
        const actualProto = new.target.prototype;

        if (Object.setPrototypeOf) { Object.setPrototypeOf(this, actualProto); }
        else { (this as any).__proto__ = actualProto; }
    }
}

@injectable()
export abstract class BaseCommand implements Command {
    abstract name: string;
    abstract aliases: string[];
    permissionLevel = PermLevel.owner;
    patronLevel = PatronLevel.none;
    // Implementation of this is such that patronLevel overrides userPatronLevel, but
    // patronLevel check effectiveLevel, taking the guild sum into consideration,
    // while userPatronLevel only looks at the specific user's level. This
    // makes it possible to, say, restrict a command to the guild only
    // after .plaid, but an individual user only needs .ridiculous
    //
    // Setting to .plaid by default so commands only have to specify it if they
    // need to override it in such a situation
    userPatronLevel = PatronLevel.plaid;
    guildOnly = false;
    abstract help: HelpText;

    @inject(TYPES.Permissions) protected permissions: Permissions;
    @inject(TYPES.Api) protected api: API;
    @inject(TYPES.Client) protected client: Client;
    @inject(TYPES.Patron) protected patron: Patron;

    async handle(content: string, message: Message): Promise<boolean> {
        const args = content.trim().split(/ +/g);
        const givenCommand = (args.shift() ?? '').toLowerCase();

        if ([this.name, ...this.aliases].map(n => n.toLowerCase()).includes(givenCommand)) {
            const userLevel = await this.permissions.userLevelFrom(message);
            if (userLevel < this.permissionLevel) {
                console.error(`[CMD] [${PermLevel[userLevel].toTitleCase()}] ${message.author.username} (${message.author.id}) does not have permission for command ${this.name} [${args}]`);
                throw new NoPermissionsError(this.permissionLevel, userLevel);
            }

            const memberPatronLevel = await this.patron.patronLevelFor(message.author);

            if (memberPatronLevel.effectiveLevel < this.patronLevel && memberPatronLevel.userLevel < this.userPatronLevel) {
                console.error(`[CMD] [${PatronLevel[memberPatronLevel.userLevel].toTitleCase()}] [${PermLevel[userLevel].toTitleCase()}] ${message.author.username} (${message.author.id}) does not have patron level for command ${this.name} [${args}]`);
                throw new PatronError(this.patronLevel, memberPatronLevel);
            }

            console.log(`[CMD] [${PatronLevel[memberPatronLevel.userLevel].toTitleCase()}] [${PermLevel[userLevel].toTitleCase()}] ${message.author.username} (${message.author.id}) ran command ${this.name} [${args}]`);
            await this.execute(args, message);

            await message.react('🎉');
            return true;
        }

        return false;
    }

    abstract execute(args: string[], message: Message): Promise<boolean>;

    async getAllyCodeForUser(user: User, message: Message) {
        console.log('getting ally code for', user.id, message.guild ? message.guild.id : 'no server');
        const response = await this.api.get(`registration/${user.id}/${message.guild ? message.guild.id : ''}`);
        const realAllyCode = response.body.get.filter(obj => obj.discordId === user.id).map(obj => obj.allyCode)[0];
        console.log(`Got ally code ${JSON.stringify(realAllyCode)} from user ${user.id}`);

        if (!realAllyCode.length) {
            await message.react('🤔');
            await message.reply(`**${user.username}** does not have an associated ally code. Please register one 😁`);
            return null;
        }

        return realAllyCode;
    };

    async getUserFromMention(mention: string) {
        if (!mention) { return; }

        if (mention.startsWith('<@') && mention.endsWith('>')) {
            mention = mention.slice(2, -1);

            if (mention.startsWith('!')) {
                mention = mention.slice(1);
            }

            console.log(`Fetching user ${mention} from mention`);
            return await this.client.users.fetch(mention);
        }
    }

    protected async clean(text: any) {
        if (text && text.constructor.name === 'Promise') {
            text = await text;
        }

        if (typeof text !== 'string') {
            text = inspect(text, {depth: 1});
        }

        text = text
            .replace(/`/g, '`' + String.fromCharCode(8203))
            .replace(/@/g, '@' + String.fromCharCode(8203))
            .replace(this.client.token, 'mfa.VkO_2G4Qv3T--NO--lWetW_tjND--TOKEN--QFTm6YGtZq9PH--4U--tG0');

        return text as string;
    }
}

export class PageError extends Error {
    constructor(message: string) {
        super(`There was an error loading the requested page [${message}]`);

        // restore prototype chain
        const actualProto = new.target.prototype;

        if (Object.setPrototypeOf) { Object.setPrototypeOf(this, actualProto); }
        else { (this as any).__proto__ = actualProto; }
    }
}

@injectable()
export abstract class SnapshotCommand extends BaseCommand {
    @inject(TYPES.BroadcastProvider) private broadcastProvider: BroadcastProvider;
    private _broadcast: Broadcast;
    // private message: Message;

    protected wantsGuild = false;
    protected wantsEmbed = true;
    protected argsHaveSearch = false;

    protected failed = [];

    async handle(content: string, message: Message): Promise<boolean> {
        let args = content.trim().split(/ +/g);
        const givenCommand = (args.shift() ?? '').toLowerCase();

        if ([this.name, ...this.aliases].map(n => n.toLowerCase()).includes(givenCommand)) {
            // this.message = message;
            let wantsScrape = false;
            if (args[0] && (args[0].endsWith('scrape') || args[0] === '-s')) {
                // remove the scrape argument
                args.shift();

                wantsScrape = true;
            }

            const searchQuery = [];

            if (!this.wantsGuild) {
                // convert args to actual ally codes
                if (args.length === 0) {
                    args.push('me');
                }
                const codes = [];
                let allyCodeNotFound = false;
                for (let code of args) {
                    code = code.replace(/\-/g, '');
                    if (/^[0-9]{9}$/.test(code) || /^g[0-9]+$/.test(code)) {
                        codes.push(code);
                    } else if (/^<@.+>$/.test(code) || code === 'me') {
                        const user = (code === 'me') ? message.author : await this.getUserFromMention(code);
                        if (user) {
                            const realCode = await this.getAllyCodeForUser(user, message);
                            if (realCode) {
                                codes.push(realCode);
                            } else {
                                allyCodeNotFound = true;
                                continue;
                            }
                        }
                    } else if (this.argsHaveSearch) {
                        searchQuery.push(code);
                    } else if (code) {
                        await message.react('🤔');
                        await message.reply(`**${code}** does not appear to be a valid ally code 🤦🏻‍♂️`);
                        allyCodeNotFound = true;
                    }
                }

                if (!codes.length) {
                    const realCode = message.author ? await this.getAllyCodeForUser(message.author, message) : undefined;
                    if (realCode) {
                        codes.push(realCode);
                    } else {
                        allyCodeNotFound = true;
                    }
                }

                if (allyCodeNotFound) {
                    await message.reply(`Please correct the above errors and try again`);
                    return true;
                }
                args = codes;
            } else {
                const codes = [];
                for (let code of args) {
                    code = code.replace(/\-/g, '');
                    if (/^[0-9]{1,9}$/.test(code)) {
                        codes.push(code);
                    } else if (this.argsHaveSearch) {
                        searchQuery.push(code);
                    } else {
                        await message.react('🤔');
                        await message.reply(`**${code}** does not appear to be a valid guild id or ally code 🤦🏻‍♂️`);
                        return true;
                    }
                }
                args = codes;
            }

            if (wantsScrape) {
                await message.react('⏳');
                console.log(`Scrape requested for ${this.wantsGuild ? 'guild' : 'player'}, [${args.join(', ')}]`);
                let completeCount = 0;
                const scrape = this.wantsGuild ? this.scrapeGuild : this.scrapeUser;
                for (const id of args) {
                    await scrape.call(this, id, async () => {
                        completeCount += 1;
                        if (completeCount === args.length) {
                            return super.handle.apply(this, [[givenCommand, ...args, ...searchQuery].join(' '), message]);
                        } else {
                            await message.react(REACT_LIST[Math.floor(Math.random() * REACT_LIST.length)]);
                        }
                    });
                }
                return true;
            } else {
                return super.handle.apply(this, [[givenCommand, ...args, ...searchQuery].join(' '), message]);
            }
        }

        return false;
    }

    protected async snapshot(url: string) {
        let start = Date.now();
        const browser = await puppeteer.launch({ headless: true });
        const page = await browser.newPage();
        await page.setViewport({ width: 1200, height: 800, deviceScaleFactor: 2 });
        await page.setExtraHTTPHeaders({
            schwartz: 'bot',
            Authorization: this.api.authHeader,
        });

        const handleError = (e: Error) => {
            console.log(`💥 Something broke [${e}]`);
            throw new PageError(e.message);
        };

        const response = await page.goto(url).catch(handleError);
        let current = Date.now();
        console.log(`⏲  Response took ${(current - start) / 1000} seconds`);
        let result;
        if (response.ok()) {
            await page.evaluateHandle('document.fonts.ready').catch(handleError);
            start = current;
            current = Date.now();
            console.log(`⏲  document.fonts.ready took ${(current - start) / 1000} seconds`);
            const card = await page.$('.card').catch(handleError);
            start = current;
            current = Date.now();
            console.log(`⏲  finding .card took ${(current - start) / 1000} seconds`);
            result = await card.screenshot().catch(handleError);
            start = current;
            current = Date.now();
            console.log(`⏲  screenshot took ${(current - start) / 1000} seconds`);
        } else {
            result = false;
        }
        await page.close();
        await browser.close();
        if (result === false) {
            throw new Error(response.status());
        }
        return result;
    }

    private async broadcast() {
        if (this._broadcast) { return this._broadcast; }
        this._broadcast = await this.broadcastProvider();
        return this._broadcast;
    }

    protected async scrapeUser(allyCode: string, completed: () => void) {
        console.log(`Starting user scrape for [${allyCode}]`);
        const broadcast = await this.broadcast();
        broadcast.subscribeUser(allyCode, completed);

        try {
            return await this.api.get(`member/scrape/${allyCode}`);
        } catch (error) {
            return { error };
        }
    }
    protected async scrapeGuild(guild: string, completed: () => void) {
        console.log(`Starting guild scrape for [${guild}]`);
        const broadcast = await this.broadcast();
        broadcast.subscribeGuild(guild, completed);

        try {
            return await this.api.get(`guild/scrape/${guild}`);
        } catch (error) {
            return { error };
        }
    }

    protected async doSnap(URL: string, channel: TextChannel | DMChannel | NewsChannel, name: string = null, message: string = null) {
        const buffer = await this.snapshot(URL);
        name = name ?? '🍺';

        if (this.wantsEmbed) {
            await channel.send({
                files: [new MessageAttachment(buffer, `${name}.png`)],
                embed: {
                    title: `${name.replace(/_/g, '')}`,
                    description: message,
                    color: 0xfce34d,
                    url: URL,
                    thumbnail: {
                        url: 'https://schwartz.hillman.me/images/Logo@2x.png',
                    },
                    image: {
                        url: `attachment://${name}.png`,
                    },
                },
            });
        } else {
            await channel.send(new MessageAttachment(buffer, `${name}.png`));
            await channel.send(`URL: ${URL}`);
        }
    }

    protected async snapAndSend(message: Message, codes: string[], URL: string, nameOverride: string = null) {
        const codeList = codes.join(',');
        const failIndex = this.failed.indexOf(codeList);

        try {
            await this.doSnap(`${this.api.baseURL}${URL}`, message.channel, nameOverride ?? codes.join('_vs_'));

            if (failIndex > -1) {
                this.failed.splice(failIndex, 1);
            }
        } catch (err) {
            if (err instanceof PageError) {
                setTimeout(() => this.snapAndSend.apply(this, arguments), 500);
                return;
            }

            if (err.response?.status !== 422 && err.message !== 422) {
                console.error(`Bad bad response [${err.message}] from [${URL}]`);
            }

            console.error(`Fetching page to snapshot failed with status [${err.message}] (${URL})`);
            if (failIndex > -1) {
                this.failed.splice(failIndex, 1);
                await message.reply(`Querying has failed too many times. Please try again in a few minutes`);
                return;
            }
            this.failed.push(codeList);
            console.error(`Error getting snapshot for [${URL}] -> [${codeList}], trying to query`);
            const scrapeMessage = await message.channel.send(`At least one of the ally codes needs to be scraped first…`);
            await scrapeMessage.react('⏳');

            let completeCount = 0;
            const scrape = this.wantsGuild ? this.scrapeGuild : this.scrapeUser;
            for (const id of codes) {
                await scrape.call(this, id, async () => {
                    completeCount += 1;
                    if (completeCount === codes.length) {
                        return this.snapAndSend.apply(this, arguments);
                    } else {
                        await message.react(REACT_LIST[Math.floor(Math.random() * REACT_LIST.length)]);
                    }
                });
            }
        }
    }

    protected async snapDM(channel: DMChannel, code: string, slug: string, message: string, suffix: string = '') {
        const URL = `${slug}/${code}${suffix}`;

        try {
            await this.doSnap(`${this.api.baseURL}${URL}`, channel, code, message);
        } catch (err) {
            if (err instanceof PageError) {
                setTimeout(() => this.snapDM.apply(this, arguments), 500);
                return;
            }

            if (err.response?.status !== 422 && err.message !== 422) {
                console.error(`Bad bad response [${err.message}] from [${URL}]`);
            }

            console.error(`Fetching page to snapshot failed with status [${err.message}] (${URL})`);
            console.error(`Error getting snapshot for [${URL}] -> [${code}]`);
        }
    }

    protected async snapReplayForCompare(message: Message, codes: string | string[], slug: string, query: string, nameOverride: string = null) {
        if (!Array.isArray(codes)) {
            codes = [codes];
        }

        const codeList = codes.join(',');
        const URL = `${slug}?${query}=${codeList}`;

        return await this.snapAndSend(message, codes, URL, nameOverride);
    }

    protected async snapReplyForAllyCodes(message: Message, codes: string | string[], slug: string, suffix: string = '') {
        if (!Array.isArray(codes)) {
            codes = [codes];
        }

        for (const code of codes) {
            const URL = `${slug}/${code}${suffix}`;
            await this.snapAndSend(message, [code], URL);
        }
    }

    protected async snapReplyForGuilds(message: Message, guilds: string[], slug, suffix: string = '') {
        if (guilds.length !== 2) {
            await message.reply(`You must give 2 and only 2 guilds for a guild compare :)`);
            return;
        }

        const URL = `${slug}/${guilds.join('/')}${suffix}`;

        return await this.snapAndSend(message, guilds, URL);
    }

    protected parseSearchFromArgs(args: string[]) {
        return args.reduce<{code: string[], search: string[]}>((combined, code) => {
            combined[/^[0-9]{1,9}$/.test(code) ? `code`: `search`].push(code);
            return combined
        }, {code: [] as string[], search: [] as string[]});
    }

    protected async doSearch(search: string[], searchSlug: string, complete: (searchResult: any) => Promise<void>) {
        const response = await this.api.search(`${searchSlug}${encodeURIComponent(search.join(' '))}`);
        await complete(response.body);
    }
}
