<template>
    <div class="mods">
        <div class="row top">
            <label class="file-label"><input type="file" id="mods-json" v-on:change="filePicked"> <span>Load Mods Export File</span></label>
            <p class="instructions">
                Download a copy of <a href="https://docs.google.com/spreadsheets/d/1aba4x-lzrrt7lrBRKc1hNr5GoK5lFNcGWQZbRlU4H18/copy" target="_gdocs">this spreadsheet</a> and follow
                the instructions to export a json file containing your mod information. Then press the button to the left and select that file.
            </p>
        </div>
        <div v-show="modsArray.length">
            <h2>5* Speed Arrows</h2>
            <div class="arrows">
                <div v-for="(count, set) in speedArrowCounts" :key="set">
                    <img :src="'/images/mods/arrow_' + set + '.png'" width="46"> {{ count }}
                </div>
            </div>
            <button class="btn btn-primary" @click="addSet">Add Mod Set</button>
            <div class="sets row">
                <div class="set"
                    v-for="(set, index) in sets"
                    :key="index"
                    @click="activateSet(index + 1)"
                    :class="{active: (index + 1) == currentSet, over: index == dragOverIndex, dragging: index == draggingIndex}"
                    draggable="true"
                    @dragstart.self="onDragStart(index, $event)"
                    @dragover.prevent="onDragOver(index, $event)"
                    @dragenter="onDragEnter(index)"
                    @dragleave.self="onDragLeave(index)"
                    @drop.prevent.stop="onDrop(index, $event)"
                >
                    <span>Speed: {{ formatSet(set) }}</span>
                    <input type="text" v-model="set.destination" @change="syncState()" @click.stop size="15" placeholder="Destination">
                    <div>
                        <div v-for="shape in shapes" :key="shape">
                            <img :src="'/images/mods/' + shape + '_' + setFor(shape, set) + '.png'" width="16"> {{ locationFor(shape, set) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="set-filter row">
                <div class="btn" v-for="set in modSets" :key="set" :class="{selected: setFilter.includes(set)}" @click="toggleFilterFor(set)">
                    <img :src="'/images/mods/square_' + set + '.png'" width="30">
                </div>
                <label>
                    <input type="checkbox" v-model="filterSelected"> <span>Hide mods already in a set</span>
                </label>
            </div>
            <div class="shapes">
                <div class="mod-list" v-for="shape in shapes" :key="shape">
                    <h2>{{ shape }}</h2>
                    <div class="mod-wrapper"
                        v-for="mod in hasAttribute(shape)"
                        :key="mod.id"
                        :mod-set="setDescriptionFor(mod)"
                        :class="{active: mod.modSet == currentSet}"
                        @click="addToActiveSet(mod)"
                    ><mod :mod="mod"></mod></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
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
                setFilter: [],
                filterSelected: false,

                dragOverIndex: null,
                draggingIndex: null,
            }
        },

        computed: {
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
                return this.modsArray.filter((mod) => mod.slot === "arrow")
                    .filter((mod) => mod.primary.type === "speed")
                    .filter((mod) => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
                    .sort((a, b) => {
                        if (+a.primary.value < +b.primary.value) { return -1; }
                        if (+a.primary.value > +b.primary.value) { return 1; }
                        if (a.set == "speed" && b.set != "speed") { return 1; }
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
            }
        },

        methods: {
            hasAttribute: function(shape) {
                let base = shape === "arrow" ? this.speedArrows : this.modsArray;
                let mods = base.filter((mod) => mod.slot === shape)
                    .filter((mod) => this.setFilter.length ? this.setFilter.includes(mod.set) : true)
                    .filter((mod) => !this.filterSelected || !mod.modSet || mod.modSet == this.currentSet);
                if (this.only === null || shape === "arrow") { return mods; }
                return mods
                    .filter((mod) => mod.has[this.only])
                    .sort((a, b) => {
                        if (+a.secondaries[this.only] < +b.secondaries[this.only]) { return -1; }
                        if (+a.secondaries[this.only] > +b.secondaries[this.only]) { return 1; }
                        return 0;
                    })
                    .reverse();
            },
            filePicked: function(evt) {
                let jsonFile = evt.target.files[0];
                if (!jsonFile) { console.warn('no file seleted', evt); return; }

                let reader = new FileReader();
                reader.onload = (loadEvt) => {
                    console.warn('Loaded', loadEvt);
                    let mods = JSON.parse(loadEvt.target.result);
                    this.mods = mods.reduce((all, mod) => {
                        let fixed = {
                            id: mod.mod_uid,
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
                            modSet: null
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
                        }

                        all[fixed.id] = fixed;

                        return all;
                    }, {});
                    this.currentSet = 0;

                    this.syncState();
                };
                reader.onerror = (loadEvt) => {
                    console.warn("Failed to load file", evt, loadEvt);
                };
                reader.onprogress = (progressEvt) => {
                    console.warn('Progress', progressEvt);
                }
                reader.readAsText(jsonFile, 'UTF-8');
            },
            addSet: function(evt) {
                this.sets.push({
                    square: null,
                    diamond: null,
                    triangle: null,
                    circle: null,
                    cross: null,
                    arrow: null,
                    speedSet: 0,
                    destination: "",
                });
                this.activateSet(this.sets.length);
                this.syncState();
            },
            activateSet: function(set) {
                this.currentSet = this.currentSet == set ? null : set;
            },
            addToActiveSet: function(mod) {
                if (this.currentSet == 0) { return; }
                let prev = this.sets[this.currentSet - 1][mod.slot];
                if (prev) {
                    this.mods[prev].modSet = null;
                    if (this.mods[prev].set == "speed") {
                        this.sets[this.currentSet - 1].speedSet -= 1;
                    }
                    if (this.mods[prev].id == mod.id) {
                        this.sets[this.currentSet - 1][mod.slot] = null;
                        this.syncState();
                        return;
                    }
                }
                mod.modSet = this.currentSet;
                this.sets[this.currentSet - 1][mod.slot] = mod.id;
                if (mod.set == "speed") {
                    this.sets[this.currentSet - 1].speedSet += 1;
                }
                this.syncState();
            },
            formatSet: function(set) {
                let speed = 0;
                let shapes = ["square", "diamond", "triangle", "circle", "cross"];

               shapes.forEach((shape) => {
                    let mod = this.mods[set[shape]];
                    if (!mod) { return; }
                    speed += +mod.secondaries.speed || 0;
                });

                let arrow = this.mods[set.arrow];
                if (arrow && arrow.primary.type == "speed") {
                    speed += +arrow.primary.value
                }

                return speed + (set.speedSet >= 4 ? " (+10%)" : "");
            },
            locationFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "N/A"; }
                return mod.location;
            },
            setFor: function(shape, set) {
                let mod = this.mods[set[shape]];
                if (!mod) { return "empty"; }
                return mod.set;
            },
            setDescriptionFor: function(mod) {
                if (!mod.modSet) { return null; }
                let set = this.sets[mod.modSet - 1];
                return mod.modSet + (set.destination.length ? ' (' + set.destination + ')' : '');
            },

            toggleFilterFor: function(set) {
                if (this.setFilter.includes(set)) {
                    this.setFilter.splice(this.setFilter.indexOf(set), 1);
                } else {
                    this.setFilter.push(set);
                }
            },

            syncState: function() {
                let storage = window.localStorage;
                storage.mods = JSON.stringify(this.mods);
                storage.sets = JSON.stringify(this.sets);
            },
            loadState: function() {
                let storage = window.localStorage;
                if (storage.mods) {
                    this.mods = JSON.parse(storage.mods);
                }
                if (storage.sets) {
                    this.sets = JSON.parse(storage.sets);
                }

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

    > div:not(:last-of-type) {
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
