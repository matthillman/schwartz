require('dotenv').config();

import './util/string';

import { Bot } from './bot';
import container, { BroadcastProvider } from './ioc/inversify.config';
import { TYPES } from './ioc/types';
import { API } from './services/api';
import { Pool } from 'pg';

const bot = container.get<Bot>(TYPES.Bot);
const api = container.get<API>(TYPES.Api);
const dbPool = container.get<Pool>(TYPES.DBPool);
const broadcastProvider = container.get<BroadcastProvider>(TYPES.BroadcastProvider);

// These 2 process methods will catch exceptions and give *more details* about the error and stack trace.
process.on('uncaughtException', (err, origin) => {
    const errorMsg = err.stack.replace(new RegExp(`${__dirname}/`, 'g'), './');
    console.error(`Uncaught Exception: ${errorMsg}`);
    console.error(`Exception origin: ${origin}`);
    // Always best practice to let the code crash on uncaught exceptions.
    // Because you should be catching them anyway.
    process.exit(1);
});

process.on('unhandledRejection', err => {
    if (err instanceof Error) {
        const errorMsg = err.stack.replace(new RegExp(`${__dirname}/`, 'g'), './');
        console.error(`Unhandled rejection: ${errorMsg}`);
    } else {
        console.error(`Unhandled rejection: ${err}`);
    }
});

async function init() {
    await api.ping();
    console.info(`Done with API init 👌`);

    dbPool.on('connect', _ => {
        console.info(`[PG] DB Pool connected`);
    });
    dbPool.on('acquire', _ => {
        console.debug(`[PG] new db client acquired [${dbPool.totalCount} total]`);
    });
    dbPool.on('remove', _ => {
        console.debug(`[PG] new db client removed [${dbPool.totalCount} total]`);
    });
    dbPool.on('error', (err: Error) => {
        console.error(`[PG] DB Pool error [${err}]`);
    });
    console.info(`Done with DB init 👌`);

    await (await broadcastProvider()).subscribe();
    console.info(`Done with Broadcast init 👌`);
    await bot.listen();
    console.info(`Done with Bot init 👌`);

    console.log(`Init Complete 🍻`);
}

init();
