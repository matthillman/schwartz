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
                    <input type="text" v-model="set.destination" @change="syncState()" @click.stop size="15" placeholder="Destination">
                    <div class="mod-list">
                        <div v-for="shape in shapes" :key="shape" :class="[`tier-${tierFor(shape, set)}`]">
                            <div class="mod-image mini" :class="[setFor(shape, set), shape, `tier-${tierFor(shape, set)}`, {'gold': pipsFor(shape, set) > 5}]"></div>
                            <span>{{ locationFor(shape, set) }}</span>
                        </div>
                    </div>
                    <button class="view-modal btn btn-primary" @click.stop="detailSet = set">View</button>
                </div>
            </div>

            <div class="set-filter row">
                <div>
                    <div class="btn" v-for="attribute in attributes" :key="attribute" :class="{selected: only == attribute}" @click="pickAttribute(attribute)">
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
                    <label v-show="!!only"><input type="checkbox" v-model="showAll"> <span>Show all mods</span></label>
                </div>
            </div>

            <div class="shapes">
                <div class="mod-list" v-for="shape in shapes" :key="shape">
                    <h2>{{ shape }}</h2>
                    <div class="filter-wrapper">
                        <v-select v-if="primaries[shape]" v-model="onlyPrimary[shape]" :options="primaries[shape]" :searchable="false"></v-select>
                    </div>
                    <div class="mod-wrapper"
                        v-for="mod in hasAttribute(shape)"
                        :key="mod.uid"
                        :mod-set="setDescriptionFor(mod)"
                        :class="{active: mod.modSet == currentSet}"
                        @click="addToActiveSet(mod)"
                    ><mod :mod="mod"></mod></div>
                </div>
            </div>
        </div>

        <modal v-if="detailSet" @close="detailSet = null">
            <h3 slot="header">{{ detailSet.destination || ('Set ' + (sets.indexOf(detailSet) + 1)) }}</h3>
            <div slot="body" class="mod-details">
                <div v-for="shape in ['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross']" :key="shape">
                    <mod :mod="mods[detailSet[shape]]" v-if="detailSet[shape]"></mod>
                    <div v-else class="mod missing">No {{ shape }} selected</div>
                </div>
            </div>
            <div slot="footer" class="bonuses">
                <div v-for="attribute in attributes" :key="attribute">{{ attribute }}: {{ formatSet(detailSet, attribute) }}</div>
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
            case 'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE': return 'critical avoidance';
            case 'UNITSTATEVASIONNEGATEPERCENTADDITIVE': return 'accuracy';

            case 'UNITSTATCRITICALCHANCE': return 'critical chance';
            case 'UNITSTATCRITICALNEGATECHANCE': return 'critical avoidance';
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
        mounted: function() {
            this.loadState();
        },
        components: {
            'mod': require('./Mod.vue')
        },
        data: function() {
            return {
                mods: {},
                sets: [],
                currentSet: 0,
                only: 'speed',
                shapes: ["square", "diamond", "triangle", "circle", "cross", "arrow"],
                modSets: ["health", "defense", "critdamage", "critchance", "tenacity", "offense", "potency", "speed"],
                attributes: ["speed", "offense", "defense", "health", "protection", "critical chance", "tenacity"],
                primaries: {
                    arrow: ['all', 'speed', 'offense', 'protection', 'health'],
                    circle: ['all', 'protection', 'health'],
                    cross: ['all', 'offense', 'protection', 'health', 'potency', 'tenacity'],
                    triangle: ['critical damage', 'offense', 'protection', 'health'],
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
                showAll: false,
                hideCompletedSets: false,

                dragOverIndex: null,
                draggingIndex: null,

                detailSet: null,
                removeSet: null,
                jsonDownload: null,
                swgoh: null,
                syncing: false,
                listening: false,
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

        created: function() {
            this.unwatch = this.$watch('swgoh', _.debounce((newName, oldName) => {
                if (oldName && /^\d{9}$/.test(oldName)) {
                    Echo.leave('mods.' + oldName);
                    this.listening = false;
                }

                if (newName && /^\d{9}$/.test(newName)) {
                    Echo.private('mods.' + newName)
                        .listen('.mods.fetched', (e) => {
                            console.log(e);
                            this.importFromSwgoh();
                        });
                    this.listening = true;
                }
            }, 250), {immediate: true});
        },

        beforeDestroy: function() {
            this.unwatch();
        },

        computed: {
            userID: function() {
                return +this.user;
            },

            modsArray: function() {
                return Object.values(this.mods);
            },
            squares: function() {
                return this.modsArray.filter((mod) => mod.slot === "square");
            },
            arrows: function() {
                return this.modsArray.filter((mod) => mod.slot === "arrow");
            },
            diamonds: function() {
                return this.modsArray.filter((mod) => mod.slot === "diamond");
            },
            triangles: function() {
                return this.modsArray.filter((mod) => mod.slot === "triangle");
            },
            circles: function() {
                return this.modsArray.filter((mod) => mod.slot === "circle");
            },
            crosses: function() {
                return this.modsArray.filter((mod) => mod.slot === "cross");
            },
            speedArrows: function() {
                let list = this.arrows;
                if (this.onlyPrimary.arrow && this.onlyPrimary.arrow !== 'all') {
                    list = list.filter((mod) => mod.primary.type === this.onlyPrimary.arrow);
                } else if (!this.onlyPrimary.arrow && !this.showAll) {
                    list = list.filter((mod) => mod.primary.type === "speed");
                }
                return list.filter((mod) => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
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
            speedArrowCounts: function() {
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

            filteredSets: function() {
                let sets = this.sets;
                if (this.hideCompletedSets) {
                    sets = sets.map((set) => {
                        set.complete = this.shapes
                            .map((shape) => {
                                return this.locationFor(shape, set);
                            })
                            .reduce((matches, location, index, locations) => {
                                return matches && location != "N/A" && (index == 0 || location == locations[index - 1]);
                            }, true);
                        return set;
                    }).filter((set) => !set.complete);
                }
                return sets;
            }
        },

        methods: {
            connected: function() {
                return this.listening && Object.keys(Echo.connector.channels).length;
            },
            hasAttribute: function(shape) {
                let base = shape === "arrow" ? this.speedArrows : this.modsArray;
                let mods = base.filter((mod) => mod.slot === shape)
                    .filter(mod => !this.onlyPrimary[shape] || this.onlyPrimary[shape] === 'all' || this.onlyPrimary[shape] === mod.primary.type)
                    .filter((mod) => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
                    .filter((mod) => !this.filterSelected || !mod.modSet || mod.modSet == this.currentSet);
                if (this.only == "speed" && shape == "arrow") {
                    return mods;
                }
                if (this.only !== null && !this.showAll) {
                    mods = mods.filter((mod) => mod.has[this.only]);
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

            filePicked: function(evt) {
                this.readFileFrom(evt, (mods) => {
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
                            modSet: (this.sets.filter((set) => set[mod.slot] == mod.mod_uid)[0] || {}).id
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
            setsPicked: function(evt) {
                this.readFileFrom(evt, (sets) => {
                    this.sets = sets;
                    this.sets.forEach((set) => {
                        this.shapes.forEach((shape) => {
                            if (!this.mods[set[shape]]) { return; }
                            this.mods[set[shape]].modSet = set.id;
                        });
                    });
                    this.currentSet = 0;
                    this.syncState();
                });
            },
            readFileFrom: function(evt, process) {
                let jsonFile = evt.target.files[0];
                if (!jsonFile) { console.warn('no file seleted', evt); return; }

                let reader = new FileReader();
                reader.onload = (loadEvt) => {
                    let result = JSON.parse(loadEvt.target.result);
                    process(result);
                };
                reader.onerror = (loadEvt) => {
                    console.error("Failed to load file", evt, loadEvt);
                };
                // reader.onprogress = (progressEvt) => {
                //     console.warn('Progress', progressEvt);
                // }
                reader.readAsText(jsonFile, 'UTF-8');
            },
            downloadSets: function() {
                this.jsonDownload = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.sets));
            },

            addSet: function(evt) {
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
            deleteSet: function(set) {
                let index = this.sets.indexOf(set);
                this.sets.splice(index, 1);
                if (this.currentSet == set) {
                    this.currentSet = null;
                }
                this.syncState();
                this.removeSet = null;
            },
            activateSet: function(set) {
                this.currentSet = this.currentSet == set ? null : set;
            },
            addToActiveSet: function(mod) {
                let set = this.sets.filter((set) => set.id == this.currentSet)[0];
                if (!set) { return; }
                let prevMod = set[mod.slot];
                if (prevMod) {
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
                let prevSet = this.sets.filter((set) => set.id == mod.modSet)[0];
                if (prevSet) {
                    prevSet[mod.slot] = null;
                    if (mod.set == "speed") {
                        prevSet.speedSet -= 1;
                    }
                }
                mod.modSet = this.currentSet;
                set[mod.slot] = mod.uid;
                if (mod.set == "speed") {
                    set.speedSet += 1;
                }
                this.syncState();
            },
            formatSet: function(set, attribute) {
                attribute = attribute || "speed";
                let total = 0;
                let shapes = ["square", "diamond", "triangle", "circle", "cross"];

               shapes.forEach((shape) => {
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

                return total + (attribute == "speed" && set.speedSet >= 4 ? " (+10%)" : "");
            },
            locationFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "N/A"; }
                return mod.location;
            },
            tierFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod)  { return 1; }
                return mod.tier;
            },
            pipsFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod)  { return 1; }
                return mod.pips;
            },
            setFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "empty"; }
                return mod.set;
            },
            setDescriptionFor: function(mod) {
                if (!mod.modSet) { return null; }
                let set = this.sets.filter((set) => set.id == mod.modSet)[0];
                if (!set) { return; }
                return set.destination;
            },

            toggleFilterFor: function(set) {
                if (this.setFilter.includes(set)) {
                    this.setFilter.splice(this.setFilter.indexOf(set), 1);
                } else {
                    this.setFilter.push(set);
                }
            },
            pickAttribute: function(attribute) {
                this.only = this.only == attribute ? null : attribute;
            },

            syncState: function() {
                let storage = window.localStorage;
                storage.mods = JSON.stringify(this.mods);
                storage.sets = JSON.stringify(this.sets);
                storage.swgoh = this.swgoh;
            },
            loadState: function() {
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
            },

            triggerSync: function() {
                if (this.swgoh == null) { return; }
                this.syncing = true;

                axios.put(window.location.href + '/' + this.swgoh)
                    .then(response => {
                        console.log(response.status);
                    });
            },

            importFromSwgoh: function() {
                if (this.swgoh == null) { return; }
                this.syncing = true;

                axios.get(window.location.href + '/' + this.swgoh)
                    .then((response) => {
                        this.mods = response.data.reduce((all, mod) => {
                            mod.modSet = (this.sets.filter((set) => set[mod.slot] == mod.uid)[0] || {}).id

                            if (mod.primary.type.indexOf('SPEED') === -1) {
                                mod.primary.value = `${mod.primary.value}%`;
                            }
                            mod.primary.type = translate(mod.primary.type, true);

                            for (const secondary in mod.secondaries) {
                                if (mod.secondaries.hasOwnProperty(secondary)) {
                                    const element = mod.secondaries[secondary];
                                    mod.secondaries[translate(secondary)] = translateValue(secondary, element);
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
                    });
            },

            // DnD
            onDragStart: function(index, evt) {
                evt.dataTransfer.effectAllowed = 'move';
                evt.dataTransfer.setData('text', index);
                this.currentSet = null;
                this.draggingIndex = index;
            },
            onDragEnter: function(index) {
                this.dragOverIndex = index;
            },
            onDragOver: function(index, evt) {
                this.dragOverIndex = index;
                evt.dataTransfer.dropEffect = 'move';
            },
            onDragLeave: function(index) {
                this.dragOverIndex = null;
            },
            onDragEnd: function() {
                this.dragOverIndex = null;
                this.draggingIndex = null;
            },
            onDrop: function(index, evt) {
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
