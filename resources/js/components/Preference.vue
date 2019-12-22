<template>
<div>
    <h2>Sets</h2>

    <div class="sets">
        <div v-for="number in [1, 2, 3]" :key="number">
            <h2>Set {{ number }}</h2>
            <div v-for="set in sets(number)" :key="set">
                <span>{{ format(set) }}</span>
                <span>
                    <vue-slider v-bind="setSlider(set, number)" v-model="preference.sets[number - 1][set]"></vue-slider>
                    <span class="value">{{ preference.sets[number - 1][set] }}</span>
                </span>
            </div>
        </div>
    </div>

    <h2>Stats</h2>
    <div class="primaries">
        <div v-for="shape in shapes" :key="shape">
            <h2>{{ shape }}</h2>
            <div v-for="attribute in primaries[shape]" :key="attribute">
                <span>{{ format(attribute) }}</span>
                <span>
                    <vue-slider v-bind="primarySlider" v-model="preference.primaries[shape][attribute]"></vue-slider>
                    <span class="value">{{ preference.primaries[shape][attribute] }}</span>
                </span>
            </div>
        </div>
    </div>


    <div class="secondaries">
        <div>
            <h2>Secondaries</h2>
            <div v-for="attribute in secondaries" :key="attribute">
                <span>{{ format(attribute) }}</span>
                <span>
                    <vue-slider v-bind="secondarySlider" v-model="preference.secondaries[attribute]"></vue-slider>
                    <span class="value">{{ preference.secondaries[attribute] }}</span>
                </span>
            </div>
        </div>
    </div>

    <div class="button-wrapper"><button class="btn btn-primary" @click.prevent="save">Save</button></div>
</div>
</template>

<script>
export default {
    props: {
        unit: String,
    },
    mounted: function() {
        console.warn(JSON.parse(this.unit));
        this.$watch(function() { return this.preference.sets[0] }, function() {
            this.doubleSets.forEach(set => {
                this.preference.sets[1][set] = this.preference.sets[0][set];
            });
        }, { deep: true });
    },
    computed: {
        primarySlider: function() { return Object.assign({}, this.slider, { width: 100 }); },
        secondarySlider: function() { return Object.assign({}, this.slider, { width: 300 }); },
    },
    data: function() {
        return {
            preference: {
                sets: [
                    {
                        health: 0,
                        defense: 0,
                        crit_damage: 0,
                        crit_chance: 0,
                        tenacity: 0,
                        offense: 0,
                        potency: 0,
                        speed: 0,
                    },
                    {
                        health: 0,
                        defense: 0,
                        crit_damage: 0,
                        crit_chance: 0,
                        tenacity: 0,
                        offense: 0,
                        potency: 0,
                        speed: 0,
                    },
                    {
                        health: 0,
                        defense: 0,
                        crit_chance: 0,
                        tenacity: 0,
                        potency: 0,
                    },
                ],
                primaries: {
                    square: {offense: 100},

                    arrow: {
                        speed: 0,
                        offense: 0,
                        health: 0,
                        protection: 0,
                        defense: 0,
                        accuracy: 0,
                        crit_avoid: 0,
                    },

                    diamond: {defense: 100},

                    triangle: {
                        crit_damage: 0,
                        crit_chance: 0,
                        offense: 0,
                        health: 0,
                        protection: 0,
                        defense: 0,
                    },

                    circle: {
                        health: 0,
                        protection: 0,
                    },

                    cross: {
                        offense: 0,
                        protection: 0,
                        health: 0,
                        potency: 0,
                        tenacity: 0,
                        defense: 0,
                    },
                },
                secondaries: {
                    speed: 0,
                    crit_chance: 0,
                    potency: 0,
                    tenacity: 0,
                    offense: 0,
                    defense: 0,
                    health: 0,
                    protection: 0,
                    offense_percent: 0,
                    defense_percent: 0,
                    health_percent: 0,
                    protection_percent: 0,
                }
            },
            value: 0,
            doubleSets: [ 'crit_damage', 'offense', 'speed' ],
            singleSets: [ 'health', 'defense', 'crit_chance', 'tenacity', 'potency' ],
            shapes: ['arrow', 'triangle', 'circle', 'cross'],
            slider: {
                width: "auto",
                height: 8,
                dotSize: 20,
                min: 0,
                max: 100,
                disabled: false,
                show: true,
                speed: 0.3,
                reverse: false,
                lazy: false,
                tooltip: false,
            },

            primaries: {
                square: ['offense'],

                arrow: [
                    'speed',
                    'offense',
                    'health',
                    'protection',
                    'defense',
                    'accuracy',
                    'crit_avoid',
                ],

                diamond: ['defense'],

                triangle: [
                    'crit_damage',
                    'crit_chance',
                    'offense',
                    'health',
                    'protection',
                    'defense',
                ],

                circle: [
                    'health',
                    'protection',
                ],

                cross: [
                    'offense',
                    'protection',
                    'health',
                    'potency',
                    'tenacity',
                    'defense',
                ],
            },

            secondaries: [
                'speed',
                'crit_chance',
                'potency',
                'tenacity',
                'offense',
                'defense',
                'health',
                'protection',
                'offense_percent',
                'defense_percent',
                'health_percent',
                'protection_percent',
            ]
        }
    },
    methods: {
        setSlider: function(set, index) {
            if (this.doubleSets.indexOf(set) > -1 && index > 1) {
                return Object.assign({}, this.primarySlider, { disabled: true, speed: 0.1 });
            }
            return this.primarySlider;
        },
        sets: function(index) {
            if (index < 3) {
                return this.doubleSets.concat(this.singleSets);
            }

            return this.singleSets;
        },
        format: function(label) {
            return label.replace('_', ' ');
        },
        save: function() {
            console.warn(this.preference);
            axios.put(`${window.location.href}`, this.preference)
                .then(response => {
                    console.warn(response);
                });
        }
    }
}
</script>

<style lang="scss" scoped>
.sets, .primaries, .secondaries, .button-wrapper {
    display: inline-block;
    width: 350px;
    margin: 0 auto 8px;
}
.sets, .primaries, .secondaries {
    font-size: 13px;
    > div {
            border: 1px solid black;
            border-radius: 8px;
            padding: 8px;

            display: flex;
            flex-direction: column;
            text-transform: capitalize;
        > div {

            > span {
                display: flex;
            }
        }

        h2 {
            font-size: 16px;
            font-weight: bold;
        }
    }
}

.sets, .primaries {
    display: inline-grid;
    grid-template-columns: repeat(2, 1fr);
    grid-row-gap: 8px;
    grid-column-gap: 8px;
}
.sets {
    width: auto;
    grid-template-columns: repeat(3, 1fr);
}
.value {
    font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
    min-width: 3ex;
    margin-left: 8px;
    text-align: right;
    display: inline-block;
}
</style>
