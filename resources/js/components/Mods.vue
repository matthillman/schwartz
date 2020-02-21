<template>
    <div class="mods">
        <div class="row top">
            <label class="file-label" v-if="user == 0"><input type="file" id="mods-json" v-on:change="filePicked"> <span>Load Mods Export File</span></label>
            <p class="instructions" v-if="user == 0">
                Download a copy of <a href="https://docs.google.com/spreadsheets/d/1aba4x-lzrrt7lrBRKc1hNr5GoK5lFNcGWQZbRlU4H18/copy" target="_gdocs">this spreadsheet</a> and follow
                the instructions to export a json file containing your mod information. Then press the button to the left and select that file.
            </p>
            <div v-if="user > 0">
                <input type="text" v-model="swgoh" placeholder="Ally code (ex: 552325555)" :disabled="syncing">
                <button class="btn btn-secondary" @click="triggerSync" :disabled="syncing || !connected()">Import from swgoh</button>
            </div>
            <p class="instructions" v-if="user > 0">
                Enter your ally code and press the yellow button on the left. Upload or download your sets on the right.
            </p>
            <div>
                Sets:
                <label class="file-label"><input type="file" id="mods-json" v-on:change="setsPicked"> <img src="/images/upload.svg" width="20"></label>
                <button class="file-label" @click="downloadSets"><img src="/images/download.svg" width="20"></button>
            </div>
        </div>
        <div v-show="modsArray.length">
            <h2>5* Speed Arrows</h2>
            <div class="arrows">
                <div v-for="(count, set) in speedArrowCounts" :key="set">
                    <div class="mod-image arrow gold" :class="[set, `tier-1`]"></div> {{ count }}
                </div>
            </div>

            <div class="row justify-content-between">
                <button class="btn btn-primary" @click="addSet">Add Mod Set</button>
                <div class="btn" :class="{'btn-primary': !hideCompletedSets, 'btn-secondary': hideCompletedSets}" @click="hideCompletedSets = !hideCompletedSets">
                    {{ hideCompletedSets ? 'Show all sets' : 'Only show sets with movement' }}
                </div>
            </div>

            <div class="sets row">
                <div class="set"
                    v-for="(set, index) in filteredSets"
                    :key="set.id"
                    @click="activateSet(set.id)"
                    :class="{active: set.id == currentSet, over: index == dragOverIndex, dragging: index == draggingIndex}"
                    draggable="true"
                    @dragstart.self="onDragStart(index, $event)"
                    @dragover.prevent="onDragOver(index, $event)"
                    @dragenter="onDragEnter(index)"
                    @dragleave.self="onDragLeave(index)"
                    @drop.prevent.stop="onDrop(index, $event)"
                >
                    <span class="delete-set" @click.stop="removeSet = set"></span>
                    <span>Speed: {{ formatSet(set) }}</span>
                    <cool-select
                        v-model="set.destination"
                        :items="units"
                        :loading="syncingUnits"
                        :placeholder="set.destination ? '' : 'Select Unit'"
                        item-value="unit_name"
                        item-text="unit_name"
                        @select="destinationUpdatedFor(set)"
                    >
                        <template slot="item" slot-scope="{ item: character }">
                            <div class="portrait-preview">
                                <img class="character" :src="`/images/units/${ character.unit_name }.png`" />
                                <div class="character-name">
                                    {{ character.unit.name }}
                                </div>
                            </div>
                        </template>

                        <template slot="selection" slot-scope="{ item: character }">
                            <div class="portrait-preview">
                                <img class="character" :src="`/images/units/${ character.unit_name }.png`" />
                                <div class="character-name">
                                    {{ character.unit.name }}
                                </div>
                            </div>
                        </template>
                    </cool-select>
                    <div class="mod-list">
                        <div v-for="shape in shapes" :key="shape" :class="[`tier-${tierFor(shape, set)}`]">
                            <div class="mod-image mini" :class="[setFor(shape, set), shape, `tier-${tierFor(shape, set)}`, {'gold': pipsFor(shape, set) > 5}]"></div>
                            <span>{{ locationFor(shape, set) }}</span>
                        </div>
                    </div>
                    <button class="view-modal btn btn-primary" @click.stop="showOverlayFor(set)">View</button>
                </div>
            </div>

            <div class="set-filter row">
                <div>
                    <div class="btn" v-for="attribute in filterAttributes" :key="attribute" :class="{selected: only == attribute}" @click="pickAttribute(attribute)">
                        {{ attribute }}
                    </div>
                </div>
                <div>
                    <div class="btn" v-for="set in modSets" :key="set" :class="{selected: setFilter.includes(set)}" @click="toggleFilterFor(set)">
                        <div class="mod-set-image tier-5" :class="[set]"></div>
                    </div>
                </div>
                <div class="checkboxes">
                    <label><input type="checkbox" v-model="filterSelected"> <span>Hide mods already in a set</span></label>
                    <label><input type="checkbox" v-model="showSetOverlays"> <span>Show set overlays</span></label>
                    <label v-show="!!only"><input type="checkbox" v-model="showAll"> <span>Show all mods</span></label>
                </div>
            </div>

            <div class="shapes" :class="{'hide-overlay': !showSetOverlays}">
                <div class="mod-list" v-for="shape in shapes" :key="shape">
                    <div class="column-title">
                        <h2>{{ shape }}</h2>
                        <span>({{ hasAttribute(shape).length }})</span>
                    </div>
                    <div class="filter-wrapper">
                        <v-select v-if="primaries[shape]" v-model="onlyPrimary[shape]" :options="primaries[shape]" :searchable="false"></v-select>
                    </div>
                    <div class="mod-wrapper"
                        v-for="mod in hasAttribute(shape)"
                        :key="mod.uid"
                        :mod-set="setDescriptionFor(mod)"
                        :class="{active: mod.modSet == currentSet && currentSet > 0}"
                        @click="addToActiveSet(mod)"
                    ><mod :mod="mod"></mod></div>
                </div>
            </div>
        </div>

        <modal v-if="detailSet" @close="detailSet = null">
            <h3 slot="header">{{ unitFor(detailSet) ? unitFor(detailSet).unit.name : ('Set ' + (sets.indexOf(detailSet) + 1)) }}</h3>
            <div slot="body" class="mod-details">
                <div v-for="shape in ['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross']" :key="shape">
                    <mod :mod="mods[detailSet[shape]]" v-if="detailSet[shape]"></mod>
                    <div v-else class="mod missing">No {{ shape }} selected</div>
                </div>
            </div>
            <div slot="footer" class="bonuses">
                <loading-indicator v-if="fetchingStats"></loading-indicator>
                <div v-else v-for="(index, attribute) in attributes" :key="attribute">{{ attribute }}: {{ formatSet(detailSet, index) }}</div>
            </div>
        </modal>

        <modal v-if="jsonDownload" @close="jsonDownload = null">
            <h3 slot="header">Download Sets JSON</h3>
            <div slot="body">
                <a :href="jsonDownload" download="sets.json" class="btn btn-primary">Download JSON</a>
            </div>
        </modal>

        <modal v-if="syncing" @close="syncing = null" no-close>
            <h3 slot="header">Pulling mods for {{ swgoh }}</h3>
            <div slot="body">
                <div class="flex-center">
                    Waiting and parsing and such
                </div>
                <loading-indicator></loading-indicator>
            </div>
        </modal>

        <modal v-if="removeSet" @close="removeSet = null">
            <h3 slot="header">Delete set</h3>
            <div slot="body">
                <div class="flex-center">
                    Are you sure you want to delete set "{{ removeSet.destination }}"? This cannot be undone.
                </div>
            </div>
            <div slot="footer">
                <button class="btn btn-danger" @click="deleteSet(removeSet)">Delete</button>
            </div>
        </modal>
    </div>
</template>

<script>
    import { CoolSelect } from "vue-cool-select";
    import { UnitStat } from '../util/swgoh-enums';

    function translate(stat, primary) {
        if (primary) {
            stat = stat.replace(/PERCENTADDITIVE$/, '');
        }
        switch(stat) {
            case 'UNITSTATSPEED': return 'speed';
            case 'UNITSTATOFFENSE': return 'offense';
            case 'UNITSTATOFFENSEPERCENTADDITIVE': return '% offense';
            case 'UNITSTATDEFENSE': return 'defense';
            case 'UNITSTATDEFENSEPERCENTADDITIVE': return '% defense';
            case 'UNITSTATMAXSHIELD': return 'protection';
            case 'UNITSTATMAXSHIELDPERCENTADDITIVE': return '% protection';
            case 'UNITSTATMAXHEALTH': return 'health';
            case 'UNITSTATMAXHEALTHPERCENTADDITIVE': return '% health';
            case 'UNITSTATACCURACY': return 'potency';
            case 'UNITSTATRESISTANCE': return 'tenacity';
            case 'UNITSTATCRITICALDAMAGE': return 'critical damage';
            case 'UNITSTATCRITICALCHANCEPERCENTADDITIVE': return 'critical chance';
            case 'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE': return 'crit avoidance';
            case 'UNITSTATEVASIONNEGATEPERCENTADDITIVE': return 'accuracy';

            case 'UNITSTATCRITICALCHANCE': return 'critical chance';
            case 'UNITSTATCRITICALNEGATECHANCE': return 'crit avoidance';
            case 'UNITSTATEVASIONNEGATE': return 'accuracy';
        }
    }
    function translateValue(stat, value) {
        switch(stat) {
            case 'UNITSTATACCURACY':
            case 'UNITSTATRESISTANCE':
            case 'UNITSTATCRITICALCHANCEPERCENTADDITIVE':
            case 'UNITSTATCRITICALDAMAGE':
            case 'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE':
            case 'UNITSTATEVASIONNEGATEPERCENTADDITIVE':
                return `${value}%`;
        }

        return value.replace(/%$/, '');
    }
    export default {
        mounted() {
            this.loadState();

            if (/^\d{9}$/.test(this.swgoh)) {
                this.loadUnits();
            }
        },
        components: {
            'mod': require('./Mod.vue').default,
            CoolSelect,
        },
        data() {
            return {
                mods: {},
                sets: [],
                currentSet: 0,
                only: 'speed',
                shapes: ["square", "diamond", "triangle", "circle", "cross", "arrow"],
                modSets: ["health", "defense", "critdamage", "critchance", "tenacity", "offense", "potency", "speed"],
                filterAttributes: ["speed", "offense", "defense", "health", "protection", "critical chance", "tenacity"],

                attributes: {
                    "speed": UnitStat.UNITSTATSPEED,
                    "tenacity": UnitStat.UNITSTATRESISTANCE,
                    "Physical": UnitStat.UNITSTATATTACKDAMAGE,
                    "health": UnitStat.UNITSTATMAXHEALTH,
                    "Special": UnitStat.UNITSTATABILITYPOWER,
                    "protection": UnitStat.UNITSTATMAXSHIELD,
                    "critical chance": "critical chance",
                    offense: 'offense',
                    defense: 'defense',
                },
                primaries: {
                    arrow: ['all', 'speed', 'offense', 'protection', 'health', 'crit avoidance'],
                    circle: ['all', 'protection', 'health'],
                    cross: ['all', 'offense', 'protection', 'health', 'potency', 'tenacity'],
                    triangle: ['all', 'critical damage', 'offense', 'protection', 'health'],
                },
                onlyPrimary: {
                    square: null,
                    diamond: null,
                    triangle: 'all',
                    circle: 'all',
                    cross: 'all',
                    arrow: 'speed',
                },
                setFilter: [],
                filterSelected: false,
                showSetOverlays: true,
                showAll: false,
                hideCompletedSets: false,

                dragOverIndex: null,
                draggingIndex: null,

                detailSet: null,
                removeSet: null,
                jsonDownload: null,
                swgoh: null,
                syncing: false,
                syncingUnits: false,
                fetchingStats: false,
                listening: false,

                units: [],
                currentStats: {},
            }
        },

        props: {
            user: {
                type: String,
                default: "0",
            },
        },

        watch: {
            swgoh(val, oldVal) {
                if (!/^\d{0,9}$/.test(val)) {
                    this.$set(this, 'swgoh', oldVal || '');
                }
            }
        },

        created() {
            this.unwatch = this.$watch('swgoh', _.debounce((newName, oldName) => {
                if (oldName && /^\d{9}$/.test(oldName)) {
                    Echo.leave('mods.' + oldName);
                    this.listening = false;
                }

                if (newName && /^\d{9}$/.test(newName)) {
                    Echo.private('mods.' + newName)
                        .listen('.mods.fetched', e => {
                            console.log(e);
                            this.importFromSwgoh();
                        });
                    this.listening = true;
                }
            }, 250), {immediate: true});
        },

        beforeDestroy() {
            this.unwatch();
        },

        computed: {
            userID() {
                return +this.user;
            },

            modsArray() {
                return Object.values(this.mods);
            },
            squares() {
                return this.modsArray.filter(mod => mod.slot === "square");
            },
            arrows() {
                return this.modsArray.filter(mod => mod.slot === "arrow");
            },
            diamonds() {
                return this.modsArray.filter(mod => mod.slot === "diamond");
            },
            triangles() {
                return this.modsArray.filter(mod => mod.slot === "triangle");
            },
            circles() {
                return this.modsArray.filter(mod => mod.slot === "circle");
            },
            crosses() {
                return this.modsArray.filter(mod => mod.slot === "cross");
            },
            speedArrows() {
                let list = this.arrows;
                if (this.onlyPrimary.arrow && this.onlyPrimary.arrow !== 'all') {
                    list = list.filter(mod => mod.primary.type === this.onlyPrimary.arrow);
                } else if (!this.onlyPrimary.arrow && !this.showAll) {
                    list = list.filter(mod => mod.primary.type === "speed");
                }
                return list.filter(mod => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
                    .sort((a, b) => {
                        if (a.primary.type == "speed" && b.primary.type != "speed") { return 1; }
                        if (a.primary.type != "speed" && b.primary.type == "speed") { return -1; }
                        if (+a.primary.value < +b.primary.value) { return -1; }
                        if (+a.primary.value > +b.primary.value) { return 1; }
                        if (a.set == "speed" && b.set != "speed") { return 1; }
                        if (a.set != "speed" && b.set == "speed") { return -1; }
                        return 0;
                    })
                    .reverse();
            },
            speedArrowCounts() {
                return this.speedArrows.reduce((counts, mod) => {
                    if (mod.pips == 5) {
                        counts[mod.set] += 1;
                    }
                    return counts;
                }, {
                    critchance: 0,
                    critdamage: 0,
                    health: 0,
                    offense: 0,
                    potency: 0,
                    speed: 0,
                    tenacity: 0,
                    defense: 0,
                });
            },

            filteredSets() {
                let sets = this.sets;
                if (this.hideCompletedSets) {
                    sets = sets.map(set => {
                        let unit = this.unitFor(set)
                        let setUnit = false;
                        if (unit) {
                            setUnit = unit.unit.name;
                        }
                        set.complete = this.shapes
                            .map(shape => {
                                return this.locationFor(shape, set);
                            })
                            .reduce((matches, location, index, locations) => {
                                return matches && (
                                    (setUnit && setUnit == location) ||
                                    (!setUnit && location != "N/A" && (index == 0 || location == locations[index - 1]))
                                );
                            }, true);
                        return set;
                    }).filter(set => !set.complete);
                }
                return sets;
            }
        },

        methods: {
            connected() {
                return this.listening && Object.keys(Echo.connector.channels).length;
            },
            hasAttribute(shape) {
                let base = shape === "arrow" ? this.speedArrows : this.modsArray;
                let mods = base.filter(mod => mod.slot === shape)
                    .filter(mod => !this.onlyPrimary[shape] || this.onlyPrimary[shape] === 'all' || this.onlyPrimary[shape] === mod.primary.type)
                    .filter(mod => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
                    .filter(mod => !this.filterSelected || !mod.modSet || mod.modSet == this.currentSet);
                if (this.only == "speed" && shape == "arrow" && this.onlyPrimary.arrow == "speed") {
                    return mods;
                }
                if (this.only !== null && !this.showAll) {
                    mods = mods.filter(mod => mod.has[this.only]);
                }
                let sortAttribute = this.only || "speed";
                return mods
                    .sort((a, b) => {
                        var attributeA = parseFloat(a.secondaries[sortAttribute], 10) || 0;
                        var attributeB = parseFloat(b.secondaries[sortAttribute], 10) || 0;
                        if (attributeA < attributeB) { return -1; }
                        if (attributeA > attributeB) { return 1; }
                        return 0;
                    })
                    .reverse();
            },

            filePicked(evt) {
                this.readFileFrom(evt, mods => {
                    this.mods = mods.reduce((all, mod) => {
                        let fixed = {
                            uid: mod.mod_uid,
                            slot: mod.slot,
                            set: mod.set,
                            level: mod.level,
                            pips: mod.pips,
                            location: mod.characterName,
                            primary: {
                                type: mod.primaryBonusType.toLowerCase(),
                                value: mod.primaryBonusValue
                            },
                            secondaries: {},
                            has: {
                                speed: false,
                                offense: false,
                                defense: false,
                                health: false,
                                protection: false,
                            },
                            modSet: (this.sets.filter(set => set[mod.slot] == mod.mod_uid)[0] || {}).id
                        };

                        for (let index = 1; index <= 4; index++) {
                            let type = mod['secondaryType_' + index];
                            if (!type) { break; }

                            fixed.secondaries[type.toLowerCase()] = mod['secondaryValue_' + index];

                            fixed.has.speed = fixed.has.speed || type === "Speed";
                            fixed.has.offense = fixed.has.offense || type === "Offense";
                            fixed.has.defense = fixed.has.defense || type === "Defense";
                            fixed.has.health = fixed.has.health || type === "Health";
                            fixed.has.protection = fixed.has.protection || type === "Protection";
                            fixed.has.tenacity = fixed.has.tenacity || type === "Tenacity";
                            fixed.has["critical chance"] = fixed.has["critical chance"] || type.toLowerCase() === "critical chance";
                        }

                        all[fixed.uid] = fixed;

                        return all;
                    }, {});
                    this.currentSet = 0;

                    this.syncState();
                });
            },
            setsPicked(evt) {
                this.readFileFrom(evt, sets => {
                    this.sets = sets;
                    this.sets.forEach(set => {
                        this.shapes.forEach(shape => {
                            if (!this.mods[set[shape]]) { return; }
                            this.mods[set[shape]].modSet = set.id;
                        });
                    });
                    this.currentSet = 0;
                    this.syncState();
                });
            },
            readFileFrom(evt, process) {
                let jsonFile = evt.target.files[0];
                if (!jsonFile) { console.warn('no file seleted', evt); return; }

                let reader = new FileReader();
                reader.onload = loadEvt => {
                    let result = JSON.parse(loadEvt.target.result);
                    process(result);
                };
                reader.onerror = loadEvt => {
                    console.error("Failed to load file", evt, loadEvt);
                };
                // reader.onprogress = progressEvt => {
                //     console.warn('Progress', progressEvt);
                // }
                reader.readAsText(jsonFile, 'UTF-8');
            },
            downloadSets() {
                this.jsonDownload = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.sets));
            },

            addSet(evt) {
                let newSet = {
                    id: (new Date).getTime(),
                    square: null,
                    diamond: null,
                    triangle: null,
                    circle: null,
                    cross: null,
                    arrow: null,
                    speedSet: 0,
                    destination: "Set " + (this.sets.length + 1),
                };
                this.sets.push(newSet);
                this.activateSet(newSet.id);
                this.syncState();
            },
            deleteSet(set) {
                let index = this.sets.indexOf(set);
                this.sets.splice(index, 1);
                if (this.currentSet == set) {
                    this.currentSet = null;
                }
                this.shapes.forEach(shape => set[shape] && (this.mods[set[shape]].modSet = null));
                this.syncState();
                this.removeSet = null;
            },
            activateSet(set) {
                this.currentSet = this.currentSet == set ? null : set;
            },
            addToActiveSet(mod) {
                let set = this.sets.filter(set => set.id == this.currentSet)[0];
                this.addModToSet(mod, set)
            },
            addModToSet(mod, set) {
                if (!set) { return; }
                let prevMod = set[mod.slot];
                if (prevMod && this.mods[prevMod]) {
                    this.mods[prevMod].modSet = null;
                    if (this.mods[prevMod].set == "speed") {
                        set.speedSet -= 1;
                    }
                    if (this.mods[prevMod].uid == mod.uid) {
                        set[mod.slot] = null;
                        this.syncState();
                        return;
                    }
                }
                let prevSet = this.sets.filter(set => set.id == mod.modSet)[0];
                if (prevSet) {
                    prevSet[mod.slot] = null;
                    if (mod.set == "speed") {
                        prevSet.speedSet -= 1;
                    }
                }
                mod.modSet = set.id;
                set[mod.slot] = mod.uid;
                if (mod.set == "speed") {
                    set.speedSet += 1;
                }
                this.syncState();
            },
            formatSet(set, attribute) {
                if (Number.isInteger(attribute)) {
                    if (!this.currentStats.final) { return 0; }
                    let finalVal = this.currentStats.final[attribute];
                    let modVal = this.currentStats.mods[attribute];
                    let suffix = '';
                    if (finalVal % 1 !== 0) {
                        suffix = '%'
                        finalVal = (finalVal * 100).toFixed(2);
                        modVal = ((modVal || 0) * 100).toFixed(2);
                    }
                    let result = `${finalVal}${suffix}`;
                    if (modVal > 0) {
                        result += ` (${modVal}${suffix})`;
                    }

                    return result;
                }
                attribute = attribute || "speed";
                let total = 0;
                let shapes = ["square", "diamond", "triangle", "circle", "cross"];

               shapes.forEach(shape => {
                    let mod = this.mods[set[shape]];
                    if (!mod) { return; }
                    total += parseFloat(mod.secondaries[attribute], 10) || 0;
                });

                let arrow = this.mods[set.arrow];
                if (arrow && arrow.primary.type == attribute && attribute == "speed") {
                    total += +arrow.primary.value
                } else if (arrow) {
                    total += parseFloat(arrow.secondaries[attribute]) || 0;
                }

                total = Math.round(total * 100) / 100;

                if (attribute == "speed") {
                    let unit = this.unitFor(set);
                    if (unit) {
                        let totalSpeed = unit.base_speed + total;
                        if (set.speedSet >= 4) {
                            totalSpeed += Math.floor(unit.base_speed / 10);
                        }
                        return `${totalSpeed} (+${total}${set.speedSet >= 4 ? " +10%" : ""})`;
                    } else {
                        return `+${total}`;
                    }
                }

                return total;
            },
            locationFor(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "N/A"; }
                return mod.location;
            },
            tierFor(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod)  { return 1; }
                return mod.tier;
            },
            pipsFor(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod)  { return 1; }
                return mod.pips;
            },
            setFor(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "empty"; }
                return mod.set;
            },
            setDescriptionFor(mod) {
                if (!mod.modSet) { return null; }
                let set = this.sets.filter(set => set.id == mod.modSet)[0];
                if (!set) { return; }
                let unit = this.unitFor(set);
                if (!unit) { return set.destination; }
                return unit.unit.name;
            },
            unitFor(set) {
                if (!set.destination || this.syncingUnits) { return; }
                let unit = this.units.filter(unit => unit.unit_name === set.destination)[0];
                if (!unit) { return; }
                return unit;
            },

            toggleFilterFor(set) {
                if (this.setFilter.includes(set)) {
                    this.setFilter.splice(this.setFilter.indexOf(set), 1);
                } else {
                    this.setFilter.push(set);
                }
            },
            pickAttribute(attribute) {
                this.only = this.only == attribute ? null : attribute;
            },

            destinationUpdatedFor(set) {
                if (!this.shapes.reduce((hasAMod, shape) => set[shape] !== null || hasAMod, false)) {
                    let unit = this.unitFor(set);

                    if (unit) {
                        let mods = Object.values(this.mods).filter(mod => mod.location == unit.unit.name && !mod.modSet);

                        mods.forEach(mod => this.addModToSet(mod, set));
                    }
                }
                this.syncState();
            },
            syncState() {
                let storage = window.localStorage;
                storage.mods = JSON.stringify(this.mods);
                storage.sets = JSON.stringify(this.sets);
                storage.swgoh = this.swgoh;
                storage.units = JSON.stringify(this.units);
            },
            loadState() {
                let storage = window.localStorage;
                if (storage.mods) {
                    this.mods = JSON.parse(storage.mods);
                }
                if (storage.sets) {
                    this.sets = JSON.parse(storage.sets);
                }
                if (storage.swgoh) {
                    this.swgoh = storage.swgoh;
                }
                if (storage.units) {
                    this.units = JSON.parse(storage.units);
                }
            },

            async triggerSync() {
                if (this.swgoh == null) { return; }
                this.syncing = true;

                let response = await axios.put(window.location.href + '/' + this.swgoh);
                console.info(response.status);
                this.loadUnits();
            },

            async importFromSwgoh() {
                if (this.swgoh == null) { return; }
                this.syncing = true;

                let response = await axios.get(window.location.href + '/' + this.swgoh);

                this.mods = response.data.reduce((all, mod) => {
                    mod.modSet = (this.sets.filter(set => set[mod.slot] == mod.uid)[0] || {}).id

                    if (mod.primary.type.indexOf('SPEED') === -1) {
                        mod.primary.value = `${mod.primary.value}%`;
                    }
                    mod.primary.type = translate(mod.primary.type, true);

                    for (const secondary in mod.secondaries) {
                        if (mod.secondaries.hasOwnProperty(secondary)) {
                            const element = mod.secondaries[secondary];
                            mod.secondaries[translate(secondary)] = translateValue(secondary, element);
                            if (mod.rolls && mod.rolls[secondary]) {
                                mod.rolls[translate(secondary)] = mod.rolls[secondary];
                                delete mod.rolls[secondary];
                            }
                            delete mod.secondaries[secondary];
                        }
                    }

                    mod.has = {
                        speed: mod.secondaries.speed !== undefined,
                        offense: mod.secondaries.offense !== undefined,
                        defense: mod.secondaries.defense !== undefined,
                        health: mod.secondaries.health !== undefined,
                        protection: mod.secondaries.protection !== undefined,
                        "critical chance": mod.secondaries["critical chance"] !== undefined,
                        tenacity: mod.secondaries.tenacity !== undefined,
                    };

                    all[mod.uid] = mod;

                    return all;
                }, {});

                this.currentSet = 0;
                this.syncState();
                this.syncing = false;

            },

            async loadUnits() {
                if (this.swgoh === null) { return; }
                this.syncingUnits = true;

                let response = await axios.get(`${window.location.origin}/home/units/${this.swgoh}`);

                this.units = response.data;
                this.syncingUnits = false;

            },

            async calcStats(set) {
                let unit = this.unitFor(set);
                if (!unit) { return; }
                this.fetchingStats = true;
                let result = await axios.post(`${window.location.origin}/home/stats`, {
                    unit: unit.id,
                    mods: this.shapes.map(shape => set[shape])
                });

                this.currentStats = result.data;
                this.fetchingStats = false;
            },

            showOverlayFor(set) {
                this.calcStats(set);
                this.detailSet = set;
            },

            // DnD
            onDragStart(index, evt) {
                evt.dataTransfer.effectAllowed = 'move';
                evt.dataTransfer.setData('text', index);
                this.currentSet = null;
                this.draggingIndex = index;
            },
            onDragEnter(index) {
                this.dragOverIndex = index;
            },
            onDragOver(index, evt) {
                this.dragOverIndex = index;
                evt.dataTransfer.dropEffect = 'move';
            },
            onDragLeave(index) {
                this.dragOverIndex = null;
            },
            onDragEnd() {
                this.dragOverIndex = null;
                this.draggingIndex = null;
            },
            onDrop(index, evt) {
                let moving = +evt.dataTransfer.getData('text');
                let moved = this.sets.splice(moving, 1)[0];
                this.sets.splice(index, 0, moved);
                this.dragOverIndex = null;
                this.draggingIndex = null;
                this.syncState();
            },
        }
    }
</script>

<style lang="scss">
.portrait-preview {
    display: flex;
    align-items: center;
    height: 36px;
    max-height: 36px;
    overflow: hidden;
    font-size: 1vmax;

    .IZ-select__input & {
        width: calc(100% - 36px);
    }

    .character {
        max-height: 100%;
        background-color: #444;
        border-radius: 3px;
    }

    .character-name {
        flex-grow: 1;
        text-align: center;
        margin: 0 4px;
    }
}

.IZ-select__input.IZ-select__input--selection-slot {
    padding-left: 0;

    input {
        padding: 6px 10px;
    }
}
</style>
<style lang="scss" scoped>
h2 {
    font-size: 16px;
    font-weight: 700;
    text-transform: capitalize;
}
.top {
    align-items: center;
    justify-content: space-around;
    .instructions {
        width: 600px;
        text-align: justify;
    }
}
.shapes {
    display: flex;
    margin-top: 16px;

    .mod-list {
        display: flex;
        flex-direction: column;
        flex-basis: 20%;
        flex-grow: 0;
        overflow: hidden;

        > .column-title {
            display: flex;
            align-items: baseline;

            > span {
                font-size: 12px;
                margin-left: 6px
            }
        }
    }
}

.arrows, .sets {
    display: flex;
    margin: 16px;

    &.arrows > div:not(:last-of-type) {
        margin-right: 16px;
    }
}

[draggable] {
    transition-property: opacity, border-color;
    transition-duration: 300ms;
    transition-timing-function: ease-in-out;
    user-select: none;
    border: 2px dashed transparent;
}
.dragging {
    opacity: 0.4;
}
.over {
    border-color: rgb(0, 0, 0);
}
</style>
