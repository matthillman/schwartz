import 'reflect-metadata';
import { Container } from 'inversify';
import { TYPES } from './types';
import { Bot } from '../bot';
import { Client, Intents } from 'discord.js';
import { MessageResponder } from '../services/message-responder';
import { Ping } from '../commands/ping';
import Enmap from 'enmap';
import { BotConfig, Environment, Settings } from '../services/settings';
import { Permissions } from '../services/permissions';
import { Pit } from '../commands/pit';
import { PitClose, PitHelp, PitHolding, PitOpen, PitPost, PitSetPostThreshold, PitSetRole, PitStarting, PitStatus } from '../commands/pit.commands';
import { API, ClientConfig } from '../services/api';
import { Broadcast } from '../services/broadcast';
import { Compare } from '../commands/compare';
import { Profile } from '../commands/profile';
import { GCompare } from '../commands/gcompare';
import { Eval } from '../commands/eval';
import { Faction } from '../commands/faction';
import { Mods } from '../commands/mods';
import { Unit } from '../commands/unit';
import { Team } from '../commands/team';
import { UnitSearch } from '../commands/unit-search';
import { Stats } from '../commands/stats';
import { Poster } from '../commands/poster';
import { Schedule } from '../commands/schedule';
import { ScheduleAdd, ScheduleExecute, ScheduleList, ScheduleRemove } from '../commands/schedule.commands';
import { Payout } from '../commands/payout';
import { MyLevel } from '../commands/my-level';
import { Configuration } from '../commands/set';
import { Whois } from '../commands/whois';
import { Register } from '../commands/register';
import { Recruiting } from '../commands/recruiting';
import { Image } from '../commands/image';
import { TW } from '../commands/tw';
import { Help } from '../commands/help';
import { CommandList } from '../services/command-list';
import { Pool } from 'pg';
import { Patron } from '../services/patron';
import { BotCommandHandler } from '../services/bot-command-handler';

const container = new Container();
const procEnv = parseInt(process.env.ENVIRONMENT);
const config: BotConfig = {
    owner: '297101898375364609',
    admins: [],
    support: [],
    env: Number.isNaN(procEnv) ? Environment.local : (procEnv as Environment),
    botGuild: '455836029295919106',
};

const clientConfig: ClientConfig = {
    id: process.env.CLIENT_ID,
    secret: process.env.CLIENT_SECRET,
    scope: process.env.CLIENT_SCOPE,
}

container.bind<string>(TYPES.Token).toConstantValue(process.env.TOKEN);
container.bind<string>(TYPES.Prefix).toConstantValue(process.env.PREFIX);
container.bind<boolean>(TYPES.PatronActive).toConstantValue(Number.parseInt(process.env.PATRON_ACTIVE) === 1);
container.bind<ClientConfig>(TYPES.ApiClient).toConstantValue(clientConfig);
container.bind<Broadcast.RedisConfig>(TYPES.RedisClient).toConstantValue({});
container.bind<string>(TYPES.ApiHost).toConstantValue(process.env.BASE_URL);
container.bind<BotConfig>(TYPES.Config).toConstantValue(config);

container.bind<Bot>(TYPES.Bot).to(Bot).inSingletonScope();
container.bind<BotCommandHandler>(TYPES.BotCommandHandler).to(BotCommandHandler).inSingletonScope();
container.bind<Client>(TYPES.Client).toConstantValue(new Client({
    ws: { intents: ['GUILD_MEMBERS', 'GUILDS', 'DIRECT_MESSAGES', 'GUILD_MESSAGES'] },
}));
container.bind<API>(TYPES.Api).to(API).inSingletonScope();

container.bind<Pool>(TYPES.DBPool).toConstantValue(new Pool({
    user: process.env.DB_USER,
    database: process.env.DB_DATABASE,
    password: process.env.DB_PASSWORD,
    host: process.env.DB_HOST ?? '127.0.0.1',
    port: process.env.DB_PORT ? parseInt(process.env.DB_PORT) : 5432,
    max: process.env.DB_MAX_CLIENTS ? parseInt(process.env.DB_MAX_CLIENTS) : 5,
}));
container.bind<Patron>(TYPES.Patron).to(Patron).inSingletonScope();
container.bind<Broadcast>(TYPES.Broadcast).to(Broadcast).inSingletonScope();
export type BroadcastProvider = () => Promise<Broadcast>;
container.bind<BroadcastProvider>(TYPES.BroadcastProvider).toProvider<Broadcast>(context => {
    return () => {
        return new Promise<Broadcast>((resolve, reject) => {
            const broadcast = context.container.get<Broadcast>(TYPES.Broadcast);
            broadcast.subscribe()
                .then(() => resolve(broadcast))
                .catch(reject)
        })
    };
})

container.bind<CommandList>(TYPES.CommandList).to(CommandList).inSingletonScope();
container.bind<MessageResponder>(TYPES.MessageResponder).to(MessageResponder).inSingletonScope();
container.bind<Settings>(TYPES.Settings).to(Settings).inSingletonScope();
container.bind<Permissions>(TYPES.Permissions).to(Permissions).inSingletonScope();

container.bind<Enmap>(TYPES.SettingsDB).toConstantValue(new Enmap({
    name: 'settings',
    fetchAll: false,
    autoFetch: true,
    cloneLevel: 'deep',
}));
container.bind<Enmap>(TYPES.ScheduleDB).toConstantValue(new Enmap({
    name: 'schedules',
    fetchAll: false,
    autoFetch: true,
    cloneLevel: 'deep',
}));
container.bind<Enmap>(TYPES.PayoutDB).toConstantValue(new Enmap({
    name: 'payouts',
    fetchAll: true,
    autoFetch: true,
    cloneLevel: 'deep',
}));
container.bind<Enmap>(TYPES.RecruitDB).toConstantValue(new Enmap({
    name: 'recruiting-watcher',
    fetchAll: true,
    autoFetch: true,
    cloneLevel: 'deep',
}));
container.bind<Enmap>(TYPES.ImageDB).toConstantValue(new Enmap({
    name: 'image-generation',
    fetchAll: false,
    autoFetch: true,
    cloneLevel: 'deep',
}));

container.bind<Eval>(TYPES.Eval).to(Eval).inSingletonScope();
container.bind<Ping>(TYPES.Ping).to(Ping).inSingletonScope();
container.bind<Stats>(TYPES.Stats).to(Stats).inSingletonScope();
container.bind<MyLevel>(TYPES.MyLevel).to(MyLevel).inSingletonScope();
container.bind<Configuration>(TYPES.GuildConfiguration).to(Configuration).inSingletonScope();

container.bind<Pit>(TYPES.Pit).to(Pit).inSingletonScope();
container.bind<PitOpen>(TYPES.PitOpen).to(PitOpen).inSingletonScope();
container.bind<PitStarting>(TYPES.PitStarting).to(PitStarting).inSingletonScope();
container.bind<PitPost>(TYPES.PitPost).to(PitPost).inSingletonScope();
container.bind<PitHolding>(TYPES.PitHolding).to(PitHolding).inSingletonScope();
container.bind<PitSetRole>(TYPES.PitSetRole).to(PitSetRole).inSingletonScope();
container.bind<PitSetPostThreshold>(TYPES.PitSetPostThreshold).to(PitSetPostThreshold).inSingletonScope();
container.bind<PitStatus>(TYPES.PitStatus).to(PitStatus).inSingletonScope();
container.bind<PitClose>(TYPES.PitClose).to(PitClose).inSingletonScope();
container.bind<PitHelp>(TYPES.PitHelp).to(PitHelp).inSingletonScope();

container.bind<Schedule>(TYPES.Schedule).to(Schedule).inSingletonScope();
container.bind<ScheduleAdd>(TYPES.ScheduleAdd).to(ScheduleAdd).inSingletonScope();
container.bind<ScheduleList>(TYPES.ScheduleList).to(ScheduleList).inSingletonScope();
container.bind<ScheduleRemove>(TYPES.ScheduleRemove).to(ScheduleRemove).inSingletonScope();
container.bind<ScheduleExecute>(TYPES.ScheduleExecute).to(ScheduleExecute).inSingletonScope();

container.bind<Compare>(TYPES.Compare).to(Compare).inSingletonScope();
container.bind<GCompare>(TYPES.GCompare).to(GCompare).inSingletonScope();
container.bind<TW>(TYPES.TWC).to(TW).inSingletonScope();
container.bind<Profile>(TYPES.Profile).to(Profile).inSingletonScope();
container.bind<Faction>(TYPES.Faction).to(Faction).inSingletonScope();
container.bind<Mods>(TYPES.Mods).to(Mods).inSingletonScope();
container.bind<Unit>(TYPES.Unit).to(Unit).inSingletonScope();
container.bind<Team>(TYPES.Team).to(Team).inSingletonScope();
container.bind<UnitSearch>(TYPES.UnitSearch).to(UnitSearch).inSingletonScope();
container.bind<Poster>(TYPES.Poster).to(Poster).inSingletonScope();
container.bind<Payout>(TYPES.Payout).to(Payout).inSingletonScope();
container.bind<Whois>(TYPES.Whois).to(Whois).inSingletonScope();
container.bind<Register>(TYPES.Register).to(Register).inSingletonScope();
container.bind<Recruiting>(TYPES.Recruiting).to(Recruiting).inSingletonScope();
container.bind<Image>(TYPES.Image).to(Image).inSingletonScope();

container.bind<Help>(TYPES.Help).to(Help).inSingletonScope();

export default container;
