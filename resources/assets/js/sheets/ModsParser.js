"use strict";

const nextPageRe = /<a\s+href="[^?]+\/mods\/\?page=([0-9]+)"\s+aria-label="Next"/m;
const namesRe = /png"\s+alt="[^"]+/gm;
const statsRe = /class="statmod-stat-value">[^<]+<\/span>\s*<span\s+class="statmod-stat-label">[^<]+/gm;
const uidRe = /data-id="([^"]+)/m;
const slotRe = /tex\.statmodmystery_[0-9]+_([0-9]+)/m;
const slotArr = ['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross'];
const setRe = /tex\.statmodmystery_([0-9]+)_[0-9]+/m;
const setArr = ['health', 'offense', 'defense', 'speed', 'critchance', 'critdamage', 'potency', 'tenacity'];
const pipsRe = /("statmod-pip")/gm;
const levelRe = /statmod-level">([0-9]+)/m;

class ModsParser {
    constructor(url) {
        this.page = 1;
        this.mods = [];
        this.url = url;
    }

    scrape() {
        console.warn("Scraping page " + this.page);
        axios.get(this.url + 'mods/?page=' + this.page)
            .then((response) => {
                let parsed = this.parse(response.data);
                console.warn("Adding [" + parsed.length + "] mods", this.mods.length);
                this.mods = this.mods.concat(parsed);
                var next = this.getNextPage();

                if (next) {
                    this.page = next;
                    this.scrape();
                } else {
                    console.warn('Done', this.mods);
                }
            }, (error) => { console.error(error) });
    }

    parse(html) {
        this.html = html;
        var mods = this.html.split(/<div\s+class="col-xs-12[^>]+>/m);
        mods.shift();
        return mods.map((mod) => this.parse_mod_(mod));
    }

    getNextPage() {
        var nextPage = nextPageRe.exec(this.html);
        return (nextPage === null) ? nextPage : nextPage[1];
    }

    parse_mod_(el) {
        var mod = {};
        mod.id = this.get_uid_(el);
        mod.slot = this.get_slot_(el);
        mod.set = this.get_set_(el);
        mod.level = this.get_level_(el);
        mod.pips = this.get_pips_(el);

        var stats = el.match(statsRe);
        stats = stats.map(this.get_stat_, this);

        mod.primary = {
            type: stats[0][1].toLowerCase(),
            value: stats[0][0]
        };
        mod.secondaries = {};

        [1, 2, 3, 4].forEach(function(index) {
            if (!stats[index]) { return; }
            mod.secondaries[stats[index][1]] = stats[index][0];
        });

        var names = el.match(namesRe);
        if (names !== null) {
            names = names.map(this.get_name_, this);
            mod.location = names[0];
            mod.name = names[1];
        }
        return mod;
    }

    get_string_(el, re) {
        var value = re.exec(el);
        if (value !== null) {
            value = value[1].trim();
        }
        return value;
    }

    get_integer_(el, re) {
        var value = re.exec(el);
        if (value !== null) {
            value = parseInt(value[1]) - 1;
        }
        return value;
    }

    get_uid_(el) {
        return this.get_string_(el, uidRe);
    }

    get_slot_(el) {
        var value = this.get_integer_(el, slotRe);
        if (value !== null) {
            value = slotArr[value];
        }
        return value;
    }

    get_set_(el) {
        var value = this.get_integer_(el, setRe);
        if (value !== null) {
            value = setArr[value];
        }
        return value;
    }
    get_pips_(el) {
        var value = el.match(pipsRe);
        if (value !== null) {
            value = value.length.toString();
        }
        return value;
    }

    get_level_(el) {
        return this.get_string_(el, levelRe);
    }

    get_stat_(el) {
        var value = /statmod-stat-value">([^<]+)/.exec(el)[1].trim();
        var name = /statmod-stat-label">([^<]+)/.exec(el)[1].trim();
        return [value, name];
    }

    get_name_(el) {
        var value = /alt="([^"]+)/.exec(el)[1].trim();
        value = value.replace(/&quot;/g, '"');
        return value;
    }
};

module.exports = ModsParser;
