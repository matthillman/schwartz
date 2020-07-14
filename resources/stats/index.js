/* eslint-disable no-console */
/* global require, __dirname, process */

const env = require('node-env-file');
env(`${__dirname}/../../.env`);

const fs = require('fs');
const Stats = require('shitty-swgoh-stats');
const statData = JSON.parse(fs.readFileSync(`${__dirname}/../../storage/app/game_data/crinolo_core`, 'utf8'));
const sts = new Stats(statData);

const express = require('express');
var bodyParser = require('body-parser');
const app = express();
const port = process.env.SWGOH_HELP_STATS_PORT || 3000;

app.use(bodyParser.json({limit:'100mb'}));

app.get('/', (req, res) => res.send('🍺'))

app.post('/api', (req, res, next) => {
	const startTime = new Date;
	const rosterMap = sts.calcPlayerStats(req.body);

	let count = 0;
	let obj = Object.create(null);
	for (let [k, v] of rosterMap) {
		let collected = Object.create(null);
		for (let [d, u] of v) {
			collected[d] = u;
		}
		count += Object.keys(collected).length;
		obj[k] = collected;
	}

	const endTime = new Date;

	req.log = `Processed ${count} units [${(endTime.getTime() - startTime.getTime()) / 1000} seconds]`;

	res.json(obj);
	next();
});

// log request
app.use((req, res, next) => {
	if (req.log) {
		console.log(req.log);
	}
	next();
});

app.listen(port, () => console.log(`Stats listening on port ${port} 🍺`));
