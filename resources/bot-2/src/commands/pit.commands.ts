import { Message, Role } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from '../ioc/types';
import { Settings } from '../services/settings';
import { Permissions, PermLevel } from '../services/permissions';
import { PitSettings } from './pit';

@injectable()
export abstract class PitCommand {
    abstract name: string;
    abstract aliases: string[];

    protected needsBoss = false;
    protected needsAdmin = false;
    protected requiresOpen = false;

    constructor(
        @inject(TYPES.Settings) protected settings: Settings,
        @inject(TYPES.Permissions) protected permissions: Permissions,
    ) { }

    async execute(args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const command = args.shift().toLowerCase();
        if (![this.name, ...this.aliases].map(n => n.toLowerCase()).includes(command)) { return false; }

        if (this.needsBoss && !pitInfo.isBoss) {
            await message.reply(`🐗🚨 You don't have permission to do this, you need ${pitInfo.pitBossMention}`);
            return true;
        }
        if (this.requiresOpen && pitInfo.currentPhase === 0) {
            await message.reply(`🐗🛑 Pit is not currently running. Please use "open" to start a run`)
            return true;
        }

        if (this.needsAdmin) {
            const userLevel = await this.permissions.userLevelFrom(message);
            if (userLevel < PermLevel.administrator) {
                await message.reply(`🐗🚨 You don't have permission to do this, you need ${pitInfo.adminMention}`);
                return true;
            }
        }

        return this.run(command, args, message, pitInfo);

    }
    abstract run(command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean>;
}

export interface PitCommandInfo {
    isBoss: boolean;
    pitBossMention: string;
    pitBossRole: Role;
    adminMention: string;
    adminRole: Role;
    currentPhase: number;
    pitSettings: PitSettings;
}

@injectable()
export class PitOpen extends PitCommand {
    name = 'open';
    aliases: string[] = ['o', 'next', 'n'];

    protected needsBoss = true;

    async run(command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const commandIsNext = command === 'next' || command === 'n';

        if (commandIsNext && pitInfo.currentPhase === 0) {
            await message.reply(`🐗🛑 Pit is not currently running. Please use "open" to start a run`)
            return true;
        }

        const force = !!args.find(a => a === 'force');

        if (pitInfo.pitSettings.holding.length) {
            if (!force) {
                await message.reply(`🐗💥 Members are still holding damage. Please signal everyone with "post" before changing phases. If you _really_ want to abort, run "${command} ${args.join(' ')} force". Else run "post" to call for damage.`);
                return true;
            } else {
                this.settings.set(message.channel.id, [], 'holding');
                this.settings.set(message.channel.id, false, 'notificationSent');
            }
        }

        const nextPhaseArg = commandIsNext ? null : parseInt(args[0]);
        const startingPercentArg = (commandIsNext ? args[0] : args[1]) ?? 100;

        let nextPhase = Number.isInteger(nextPhaseArg) ? nextPhaseArg : (pitInfo.currentPhase + 1);
        if (nextPhase > 4) {
            nextPhase = 0;

            await message.channel.send(`🐗 ${pitInfo.pitBossMention} 🍾🍻 Phase 4 complete! Raid done! Wooooo!`);
        }

        console.log(`Open done`, message.channel.id, pitInfo.currentPhase, nextPhase, startingPercentArg)

        this.settings.set(message.channel.id, nextPhase, 'phase');
        this.settings.set(message.channel.id, startingPercentArg, 'starting');
        console.log(`props saved`);
        let response = '';
        if (pitInfo.currentPhase > 0) {
            response += `🐗 ${pitInfo.pitBossMention} Phase ${pitInfo.currentPhase} complete!\n\n`;
        }
        if (nextPhase > 0) {
            response += `🐗 ${pitInfo.pitBossMention} Phase ${nextPhase} now open and ready for damage (starting at ${startingPercentArg}%)`;
        }
        console.log(`RESPONSE`, response);
        if (response.length) {
            console.log('SENDING', response);
            await message.channel.send(response);
        }

        return true;
    }
}

@injectable()
export class PitStarting extends PitCommand {
    name = 'starting';
    aliases: string[] = ['start', 'st'];

    protected needsBoss = true;
    protected requiresOpen = true;

    async run(_command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const amount = parseFloat(args[0]);

        if (!Number.isNaN(amount)) {
            this.settings.set(message.channel.id, amount, 'starting');
            await message.reply(`🐗 Start percent for phase ${pitInfo.currentPhase} updated to ${amount}%`);
        } else {
            await message.reply(`🐗🛑 "${args[0]}" doesn't parse as a number. Please try again`);
            return true;
        }

        const total = pitInfo.pitSettings.holding.reduce((tot, cur) => tot + cur.amount, 0);
        const gap = 100 - amount;

        if (!pitInfo.pitSettings.notificationSent && total >= (pitInfo.pitSettings.postThreshold - gap)) {
            await message.channel.send(`${pitInfo.pitBossMention}Phase ${pitInfo.currentPhase} is loaded with ${total.toFixed(2)}% damage! Post threshold reached!`);
            this.settings.set(message.channel.id, true, 'notificationSent');
        }
    }

}

@injectable()
export class PitPost extends PitCommand {
    name = 'post';
    aliases: string[] = ['p'];

    protected needsBoss = true;
    protected requiresOpen = true;

    async run(_command: string, _args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        if (!pitInfo.pitSettings.holding.length) {
            await message.reply(`🐗 Hey… no one is holding any damage…`);
            return true;
        }

        const mentions = pitInfo.pitSettings.holding.reduce((msg, cur) => `${msg} <@${cur.id}>`, '');

        await message.channel.send(`🐗 Post your damage for phase ${pitInfo.currentPhase}!\n\n${mentions}`);

        this.settings.set(message.channel.id, [], 'holding');
        this.settings.set(message.channel.id, false, 'notificationSent');

        await message.channel.send(`🐗 ${pitInfo.pitBossMention} Post message sent for phase ${pitInfo.currentPhase}. You can now open the next phase.`);
        return true;
    }
}

@injectable()
export class PitHolding extends PitCommand {
    name = 'hold';
    aliases: string[] = ['holding', 'h'];

    protected requiresOpen = true;

    async run(_command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const amount = parseFloat(args[0]);
        const memberIndex = pitInfo.pitSettings.holding.findIndex(m => m.id === message.author.id);

        if (amount > 0) {
            if (memberIndex >= 0) {
                pitInfo.pitSettings.holding[memberIndex].amount = amount;
            } else {
                pitInfo.pitSettings.holding.push({
                    id: message.author.id,
                    name: message.author.username,
                    amount,
                });
            }
        } else if (amount === 0) {
            if (memberIndex >= 0) {
                pitInfo.pitSettings.holding.splice(memberIndex, 1);
            }
        } else {
            await message.reply(`🐗🛑 "${args[0]}" doesn't parse as a number. Please try again`);
            return true;
        }

        this.settings.set(message.channel.id, pitInfo.pitSettings.holding, 'holding');

        await message.react('🐗');

        const total = pitInfo.pitSettings.holding.reduce((tot, cur) => tot + cur.amount, 0);
        const gap = 100 - pitInfo.pitSettings.starting;

        console.log(`${pitInfo.pitBossMention}${pitInfo.currentPhase} : ${total} >= (${pitInfo.pitSettings.postThreshold} - (100 - ${pitInfo.pitSettings.starting})) [${pitInfo.pitSettings.postThreshold - gap}] [${pitInfo.pitSettings.notificationSent}]`);

        if (total >= (pitInfo.pitSettings.postThreshold - gap)) {
            if (!pitInfo.pitSettings.notificationSent) {
                await message.channel.send(`${pitInfo.pitBossMention}${pitInfo.currentPhase} is loaded with ${total.toFixed(2)}% damage! Post threshold reached!`);
                this.settings.set(message.channel.id, true, 'notificationSent');
            }
        } else {
            this.settings.set(message.channel.id, false, 'notificationSent');
        }

        return true;
    }
}

@injectable()
export class PitSetRole extends PitCommand {
    name = 'setRole';
    aliases: string[] = [];

    protected needsAdmin = true;

    async run(_command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const newRoleSearch = (args.join(' ') || 'Pit Boss').toLowerCase();
        const newPitBossRole = message.guild.roles.cache.find(r => r.name.toLowerCase() === newRoleSearch);

        if (!newPitBossRole) {
            await message.reply(`Looked for boss role "${newRoleSearch}", but I didn't find a role with that name. You must define a boss role before you can use this feature.`);
        } else {
            this.settings.set(message.channel.id, newPitBossRole.name, 'bossRole');
            await message.reply(`🐗 bossRole updated to <@&${newPitBossRole.id}>`);
        }

        return true;
    }
}

@injectable()
export class PitSetPostThreshold extends PitCommand {
    name = 'setPostThreshold';
    aliases: string[] = [];

    protected needsBoss = true;

    async run(_command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const amount = parseFloat(args[0]);

        if (amount > 0) {
            this.settings.set(message.channel.id, amount, 'postThreshold');
            await message.reply(`🐗 Post threshold notification updated to ${amount}%`);
        } else {
            await message.reply(`🐗🛑 "${args[0]}" doesn't parse as a number. Please try again`);
            return false;
        }

        const total = pitInfo.pitSettings.holding.reduce((tot, cur) => tot + cur.amount, 0);
        const gap = 100 - pitInfo.pitSettings.starting;

        if (total >= (amount - gap)) {
            if (!pitInfo.pitSettings.notificationSent) {
                await message.channel.send(`${pitInfo.pitBossMention}Phase ${pitInfo.currentPhase} is loaded with ${total.toFixed(2)}% damage! Post threshold reached!`);
                this.settings.set(message.channel.id, true, 'notificationSent');
            }
        }

        return true;
    }
}

@injectable()
export class PitStatus extends PitCommand {
    name = 'status';
    aliases: string[] = ['s'];

    protected requiresOpen = true;

    @inject(TYPES.ApiHost) apiHost: string;

    async run(_command: string, _args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const total = pitInfo.pitSettings.holding.reduce((tot, cur) => tot + cur.amount, 0);
        const memberCount = pitInfo.pitSettings.holding.length;
        const sorted = pitInfo.pitSettings.holding.sort((a, b) => a > b ? -1 : (a < b ? 1 : 0))

        const damagePostedAt = pitInfo.pitSettings.postThreshold - (100 - pitInfo.pitSettings.starting);
        await message.channel.send({
            embed: {
                title : `Challenge Rancor: Phase ${pitInfo.currentPhase} Summary`,
                description: `${memberCount} members holding **${total.toFixed(2)}%** damage
Boss health level at **${pitInfo.pitSettings.starting}%**
Posting damage at **${damagePostedAt.toFixed(2)}%**
Damage needed: **${(damagePostedAt - total).toFixed(2)}%**
\`\`\`
${sorted.reduce((c, m) => `${c}${`${m.amount.toFixed(2)}`.padStart(5)}%: ${m.name}\n`, '')}
\`\`\``,
                color: 0xfce34d,
                footer: {
                  icon_url: `${this.apiHost}/images/Logo@2x.png`,
                  text: 'Frax Bot',
                },
                thumbnail: {
                  url: `${this.apiHost}/images/rancor.png`,
                },
            },
        });

        return true;
    }
}

@injectable()
export class PitClose extends PitCommand {
    name = 'close';
    aliases: string[] = [];

    protected requiresOpen = true;

    async run(_command: string, args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        if (pitInfo.pitSettings.holding.length && args[0] !== 'force') {
            await message.reply(`🐗 Hey… ${pitInfo.pitSettings.holding.length} members claim to be holding damage. If you _really_ want to abort, run "close force". Else run "post" to call for damage.`);
            return false;
        }

        this.settings.set(message.channel.id, 0, 'phase');
        this.settings.set(message.channel.id, [], 'holding');
        this.settings.set(message.channel.id, false, 'notificationSent');

        await message.channel.send(`🐗 ${pitInfo.pitBossMention} Pit is closed!`);
        return true;
    }
}

@injectable()
export class PitHelp extends PitCommand {
    name = 'help';
    aliases: string[] = [];

    async run(_command: string, _args: string[], message: Message, pitInfo: PitCommandInfo): Promise<boolean> {
        const helpText = `Commands:
\`\`\`
For everyone [command <argument> (alias)]:
hold <amount> (holding|h)
    Indicate that you are holding amount% damage and are awaiting orders
    Run again to update your amount
    Run with 0 to indicate you are no longer holding and need to cancel
status (s)
    Get a pretty status of who is patiently holding damage

For @${pitInfo.pitBossRole.name}:
open <phase> <starting> (o)
    Mark a phase as open for damage
    Set the current % to <starting>
next <starting> (n)
    Move to the next phase
    Set the current % to <starting>
starting <amount> (start|st)
    Set the current % to <starting> for the current phase
close
    Mark the raid as over
post (p)
    Signal all members who are holding to post damage

@${pitInfo.adminRole?.name ?? pitInfo.adminRole ?? 'Admin'}:
setRole
    Set the pit boss role
    currently: "${pitInfo.pitBossRole.name}"
setPostThreshold
    Set the damage threshold that triggers a notification to the boss role
    currently: "${pitInfo.pitSettings.postThreshold}%"
\`\`\``
        console.log(`Sending Help`, helpText);
        await message.channel.send(helpText);
        return true;
    }
}
