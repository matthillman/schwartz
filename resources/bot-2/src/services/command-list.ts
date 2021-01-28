import { injectable, inject } from 'inversify';
import { TYPES } from '../ioc/types';

import { Command } from '../commands/command';
import { Compare } from '../commands/compare';
import { Eval } from '../commands/eval';
import { Faction } from '../commands/faction';
import { GCompare } from '../commands/gcompare';
import { Image } from '../commands/image';
import { Mods } from '../commands/mods';
import { MyLevel } from '../commands/my-level';
import { Payout } from '../commands/payout';
import { Ping } from '../commands/ping';
import { Pit } from '../commands/pit';
import { Poster } from '../commands/poster';
import { Profile } from '../commands/profile';
import { Recruiting } from '../commands/recruiting';
import { Register } from '../commands/register';
import { Schedule } from '../commands/schedule';
import { Configuration } from '../commands/set';
import { Stats } from '../commands/stats';
import { Team } from '../commands/team';
import { TW } from '../commands/tw';
import { Unit } from '../commands/unit';
import { UnitSearch } from '../commands/unit-search';
import { Whois } from '../commands/whois';

@injectable()
export class CommandList {
    private commands: Command[];

    constructor(
        @inject(TYPES.Eval) evl: Eval,
        @inject(TYPES.Ping) ping: Ping,
        @inject(TYPES.Stats) stats: Stats,
        @inject(TYPES.MyLevel) myLevel: MyLevel,
        @inject(TYPES.GuildConfiguration) config: Configuration,

        @inject(TYPES.Pit) pit: Pit,
        @inject(TYPES.Schedule) schedule: Schedule,
        @inject(TYPES.Compare) compare: Compare,
        @inject(TYPES.GCompare) gcompare: GCompare,
        @inject(TYPES.TWC) twc: TW,
        @inject(TYPES.Profile) profile: Profile,
        @inject(TYPES.Faction) faction: Faction,
        @inject(TYPES.Mods) mods: Mods,
        @inject(TYPES.Unit) unit: Unit,
        @inject(TYPES.Team) team: Team,
        @inject(TYPES.UnitSearch) unitSearch: UnitSearch,
        @inject(TYPES.Poster) poster: Poster,
        @inject(TYPES.Payout) payout: Payout,
        @inject(TYPES.Whois) whois: Whois,
        @inject(TYPES.Register) register: Register,
        @inject(TYPES.Recruiting) recruiting: Recruiting,
        @inject(TYPES.Image) image: Image,
    ) {
        this.commands = [
            evl,
            ping,
            stats,
            myLevel,
            config,

            pit,
            schedule,
            compare,
            gcompare,
            twc,
            profile,
            faction,
            mods,
            unit,
            team,
            unitSearch,
            poster,
            payout,
            whois,
            register,
            recruiting,
            image,
        ];

        for (const command of this.commands) {
            if (command.init) {
                command.init();
            }
        }
    }

    get list() {
        return this.commands;
    }
}
