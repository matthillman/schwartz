
global.axios = require('axios');

let user = process.argv[2];

var ModsParser = require('./ModsParser');
let p = new ModsParser('https://swgoh.gg/u/'+ user + '/');

p.scrape();