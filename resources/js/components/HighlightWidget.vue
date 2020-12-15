<template>
    <div class="set-filter row">
        <span class="sort-label note">Highlight based on:</span>
        <div>
            <div class="btn" v-for="stat in stats" :key="stat" :class="{selected: selected && stat && selected == stat}" @click="updateSelected(stat)">
                <tooltip :disabled="['stars', 'none'].includes(stat)">
                    <span>{{ labelFor(stat) }}</span>
                    <template #tooltip>
                        <div class="tooltip-wrapper">
                            <template v-if="stat === 'relic'">
                                <ul>
                                    <li class="demo-swatch tier-3">&gt;= r5</li>
                                    <li class="demo-swatch tier-0">&lt; r5</li>
                                </ul>
                            </template>
                            <template v-if="stat === 'power'">
                                <ul>
                                    <li class="demo-swatch tier-3">&gt;= 17,500</li>
                                    <li class="demo-swatch tier-2">&gt;= 16,500</li>
                                    <li class="demo-swatch tier-0">&lt; 16,500</li>
                                </ul>
                            </template>
                            <template v-else-if="stat === 'power-plus'">
                                <ul>
                                    <li class="demo-swatch tier-3">&gt;= 23,000</li>
                                    <li class="demo-swatch tier-2">&gt;= 22,000</li>
                                    <li class="demo-swatch tier-1">&gt;= 21,000</li>
                                    <li class="demo-swatch tier-0">&lt; 21,000</li>
                                </ul>
                            </template>
                            <template v-else-if="stat === 'power-stars'">
                                <ul>
                                    <li class="demo-swatch tier-3">&gt;= 17,700 and 7⭐️</li>
                                    <li class="demo-swatch tier-2">&gt;= 16,500 and 7⭐️</li>
                                    <li class="demo-swatch tier-0">&lt; 16,500 or sub–7⭐️</li>
                                </ul>
                            </template>
                            <template v-else-if="stat === 'gear'">
                                <ul>
                                    <li>G13+ will be colored as per the gear rings</li>
                                    <li class="demo-swatch tier-3">G12</li>
                                    <li class="demo-swatch tier-2">&gt;= G10</li>
                                    <li class="demo-swatch tier-0">&lt; G10</li>
                                </ul>
                            </template>
                            <template v-else-if="stat === 'mods'">
                                <p>If mod stat goals are defined for the character in the squad:</p>
                                <ul>
                                    <li class="demo-swatch tier-3">Tier 1</li>
                                    <li class="demo-swatch tier-2">Tier 2</li>
                                    <li class="demo-swatch tier-1">Tier 3</li>
                                    <li class="demo-swatch tier-4">Unit meets the goals for a tier but a stat relation is off</li>
                                    <li class="demo-swatch tier-0">sub-Tier 3</li>
                                </ul>
                                <p>If no stat goals are defined, then it higlights per <pre>gear</pre></p>
                            </template>
                        </div>
                    </template>
                </tooltip>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['starting'],
        data: function() {
            return {
                stats: [
                    'none',
                    'stars',
                    'power',
                    'power-plus',
                    'power-stars',
                    'gear',
                    'relic',
                    'mods',
                ],
                selected: '',
            }
        },
        mounted: function() {
            this.selected = this.starting;
        },
        methods: {
            updateSelected(stat) {
                this.$root.highlight = stat;
                this.selected = stat;
            },
            labelFor(stat) {
                switch (stat) {
                    case 'stars': return '★';
                    case 'power-stars': return 'power/★';
                    case 'power-plus': return 'power+';
                    default: return stat;
                }
            }
        },
    }
</script>

<style lang="scss" scoped>
@import "../../sass/_variables.scss";
.sort-label {
    margin-right: 4px;
}

.btn {
    padding: 2px .75rem;
    cursor: pointer;

    .radiant-back & {
        background-color: $light-navy;

        color: $sw-yellow;

        &.selected {
            background-color: $bg-light-navy;
            color: $white
        }
    }
}

.row {
    margin-right: 0;
    margin-left: 0;
}

.tooltip-wrapper {
    text-align: left;

    ul:not(:first-child) {
        padding-left: 16px;
    }

    pre {
        color: $white;
        display: inline;
    }
}
</style>