import { Message } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { PermLevel } from '../services/permissions';
import { BaseCommand, NoPermissionsError, HelpText, CommandCategory } from './command';
import { ScheduleCommand, ScheduleList, ScheduleExecute, ScheduleAdd, ScheduleRemove } from './schedule.commands';

@injectable()
export class Schedule extends BaseCommand {
    name = 'schedule';
    aliases: string[] = [];
    permissionLevel = PermLevel.user;
    help: HelpText = {
        category: CommandCategory.misc,
        description: 'Echoes a pre-defined schedule creation message.',
        usage: `
        schedule [event] [date (eg. today or tomorrow)]
        schedule add [event] [channel] [command]
        schedule remove [event]
        `,
    };

    private commands: ScheduleCommand[];

    constructor(
        @inject(TYPES.ScheduleAdd) add: ScheduleAdd,
        @inject(TYPES.ScheduleList) private list: ScheduleList,
        @inject(TYPES.ScheduleRemove) remove: ScheduleRemove,
        @inject(TYPES.ScheduleExecute) private exe: ScheduleExecute,
    ) {
        super();

        this.commands = [
            add,
            list,
            remove,
        ];
    }

    async execute(args: string[], message: Message): Promise<boolean> {
        const command = (args.shift() || '').toLowerCase();

        if (!command.length) {
            await this.list.execute([this.list.name, command, ...args], message);
            return true;
        }

        for (const cmd of this.commands) {
            try {
                const handled = await cmd.execute([command, ...args], message);
                if (handled) { return true; }
            } catch (err) {
                if (err instanceof NoPermissionsError) {
                    // We found the right command, just didn't have permissions
                    return true;
                }
                console.error(`[SCHEDULE] ${err}`);
            }
        }

        await this.exe.execute([this.exe.name, command, ...args], message);

        return true;
    }

}
