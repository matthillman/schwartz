require('dotenv').config();

import { Bot } from './bot';
import container, { BroadcastProvider } from './ioc/inversify.config';
import { TYPES } from './ioc/types';
import { API } from './services/api';

const bot = container.get<Bot>(TYPES.Bot);
const api = container.get<API>(TYPES.Api);
const broadcastProvider = container.get<BroadcastProvider>(TYPES.BroadcastProvider);

// These 2 process methods will catch exceptions and give *more details* about the error and stack trace.
process.on('uncaughtException', (err) => {
    const errorMsg = err.stack.replace(new RegExp(`${__dirname}/`, 'g'), './');
    console.error(`Uncaught Exception: ${errorMsg}`);
    // Always best practice to let the code crash on uncaught exceptions.
    // Because you should be catching them anyway.
    process.exit(1);
});

process.on('unhandledRejection', err => {
    console.error(`Unhandled rejection: ${err}`);
});

async function init() {
    await api.ping();
    console.info(`Done with API init 👌`);
    await bot.listen();
    console.info(`Done with Bot init 👌`);
    await (await broadcastProvider()).subscribe();
    console.info(`Done with Broadcast init 👌`);

    console.log(`Init Complete 🍻`);
}

init();
