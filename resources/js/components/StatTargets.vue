<template>
    <div class="row no-margin align-items-center no-wrap">
        <button class="btn btn-link btn-icon" :class="{'with-text' : !hasStatsDefined}" @click="showing = !showing">
            <ion-icon :name="hasStatsDefined ? 'pencil' : 'add-circle'" size="small"></ion-icon>
            <span v-if="!hasStatsDefined">Add stat targets</span>
        </button>

        <div v-if="hasStatsDefined" class="column">
            <div v-for="(stat, key) in mutableStats[squad.leader_id]" :key="stat" class="row no-margin align-items-center stat-slash">
                <ion-icon v-if="key === 'power'" name="flash" size="micro"></ion-icon>
                <span v-else class="mod-set-image tier-5 mini" :class="iconForStatKey(key)"></span>
                <span>{{ stat.tier.map(s => s > 10000 ? parseInt(s / 1000) + 'K' : s ).join('/') }}</span>
            </div>
        </div>

        <modal v-if="showing" @close="showing = false">
            <template #header><h3>Set Stat Targets</h3></template>
            <template #body>
                <div class="row no-margin">
                    <div class="col-3 column tab-column">
                        <div v-for="char_id in members" :key="char_id" class="char-wrapper" :class="{'glass-back': selected == char_id}" @click="selected = char_id">
                            <div class="column char-image-column">
                                <div class="char-image-square medium" :class="units[char_id].alignment">
                                    <img :src="`/images/units/${char_id}.png`">
                                </div>
                                <div class="char-name">{{ units[char_id].name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-9 content-column">
                        <h4>{{ units[selected].name }}</h4>

                        <div class="add-row row align-items-end no-margin">

                            <div class="column grow">
                                    <div class="row no-margin align-items-center">
                                        <span>Add a New Stat Target</span>
                                        <help size="small">Stats can only be added once</help>
                                    </div>

                                <v-select :options="unusedStats" v-model="newStat" :clearable="false">
                                    <template v-slot:option="stat">
                                        <div class="row no-margin align-items-center">
                                            <ion-icon v-if="stat.key === 'power'" name="flash" size="micro"></ion-icon>
                                            <span v-else class="mod-set-image tier-5 mini" :class="stat.key"></span>
                                            <span>{{ stat.label }}</span>
                                        </div>
                                    </template>
                                    <template v-slot:selected-option="stat">
                                        <div class="row no-margin align-items-center">
                                            <ion-icon v-if="stat.key === 'power'" name="flash" size="micro"></ion-icon>
                                            <span v-else class="mod-set-image tier-5 mini" :class="stat.key"></span>
                                            <span>{{ stat.label }}</span>
                                        </div>
                                    </template>
                                </v-select>
                            </div>

                            <button class="btn btn-primary striped" @click="addStat">
                                <span>Add</span>
                            </button>
                        </div>
                        <collapsable v-for="stat in sortedStats" :key="stat.value" :value="openCard == stat.value" v-on:input="openCard = ($event ? stat.value : null)" styleClass="stat-target-container">
                            <template #top-trigger="{open}">
                                <div class="row no-margin align-items-center">
                                    <ion-icon :name="open ? `chevron-down` : `chevron-forward`" size="small"></ion-icon>

                                    <ion-icon v-if="stat.key === 'power'" name="flash" size="micro"></ion-icon>
                                    <span v-else class="mod-set-image tier-5 mini" :class="stat.key"></span>
                                    <span>{{ stat.label }}</span>
                                </div>
                            </template>

                            <div class="stat-wrapper">
                                <label class="row no-margin align-items-end input-group" v-for="tier in [0, 1, 2]" :key="tier">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Tier {{ tier + 1 }}</span>
                                    </div>
                                    <input type="text" class="form-control" size="8" v-model="selectedStats[stat.value].tier[tier]">
                                </label>

                                <div class="row no-margin align-items-center">
                                    <strong>Add a Relation Constraint to:</strong>
                                    <help>Note that you must have tiers defined for relations to be considered</help>
                                </div>

                                <div class="row no-margin justify-content-around tabs">
                                    <div v-for="char_id in members.filter(m => m !== selected)" :key="char_id" :class="{'selected': related[stat.value] == char_id, 'related': selectedStats[stat.value].related[char_id]}" @click="showRelated(stat, char_id)">
                                        <div class="column char-image-column">
                                            <tooltip>
                                                <div class="char-image-square small" :class="units[char_id].alignment">
                                                    <img :src="`/images/units/${char_id}.png`">
                                                </div>
                                                <template #tooltip>
                                                    <div class="char-name">{{ units[char_id].name }}</div>
                                                </template>
                                            </tooltip>
                                        </div>
                                    </div>
                                </div>

                                <div class="stat-target-container" v-if="hasRelated(stat.value)">
                                    <div class="stat-wrapper form-group">
                                        <label for="related" class="row no-margin align-items-center">
                                            <span>Relation to {{ units[related[stat.value]].name }}</span>
                                            <help>
                                                <p class="white-text">
                                                    Enter a flat value or a formula. Use <strong>{{ units[related[stat.value]].name | acronymize }}</strong>
                                                    as a placeholder for {{ units[related[stat.value]].name }}’s {{ stat.label }} and <strong>{{ units[selected].name | acronymize}}</strong>
                                                    as a placeholder for {{ units[selected].name }}’s {{ stat.label }}.
                                                </p>
                                                <p class="white-text">Valid equality operators: =, &lt;, &gt;, &lt;=, &gt;=</p>
                                                <p class="white-text">Please note: the equation parser is still a work in progress. Please simplify your equaitons such that:
                                                    <ul>
                                                        <li>{{ units[selected].name | acronymize }} only appears once</li>
                                                        <li>There are no parenthesis on the side of the equaiton that contains {{ units[selected].name | acronymize }}</li>
                                                    </ul>
                                                </p>

                                            </help>
                                        </label>
                                        <input type="text" :placeholder="relatedPlaceholder(stat)" class="form-control" size="8" v-model="selectedStats[stat.value].related[related[stat.value]]">
                                    </div>
                                </div>

                            </div>
                        </collapsable>
                    </div>
                </div>
            </template>

            <template #footer>
                <div v-if="saved" class="success">Saved!</div>
                <div v-if="error" class="danger">{{ error }}</div>
                <button class="btn btn-secondary striped btn-icon with-text" @click="save()">
                    <ion-icon name="save" size="medium"></ion-icon>
                    <span>Save</span>
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import { UnitStat } from '../util/swgoh-enums';
const acronymize = (str) => str.split(/(\s|[A-Z]\w+)/).map((w) => w ? w[0].toUpperCase().trim() : '').join('');

export default {
    props: {
        squad: Object,
        units: Object,
    },
    filters: {
        acronymize: acronymize,
    },
    mounted() {
        this.newStat = this.unusedStats[0];
        this.selected = this.squad.leader_id;
        for (const char in this.mutableStats) {
            for (const stat in this.mutableStats[char]) {
                const statGroup = this.mutableStats[char][stat];
                const hasTier = statGroup.tier.length;

                for (const relation in statGroup.related) {
                    if (statGroup.related[relation].length) {
                        let formula = statGroup.related[relation]
                            .replace(relation, acronymize(this.units[relation].name))
                            .replace(char, acronymize(this.units[char].name));
                        statGroup.related[relation] = formula;
                    }
                }
            }
        }
    },
    data() {
        return {
            showing: false,
            selected: null,
            newStat: null,
            mutableStats: this.squad.stats,
            openCard: null,
            related: {},
            stats: [
                {label: 'Speed', value: UnitStat.UNITSTATSPEED, key: 'speed', short: 'Speed', },
                {label: 'Offense', value: UnitStat.UNITSTATATTACKDAMAGE, key: 'offense', short: 'Off', },
                {label: 'Health', value: UnitStat.UNITSTATMAXHEALTH, key: 'health', short: 'Health', },
                {label: 'Protection', value: UnitStat.UNITSTATMAXSHIELD, key: 'health', short: 'Prot', },
                {label: 'Special Offense', value: UnitStat.UNITSTATABILITYPOWER, key: 'offense', short: 'S Off', },
                {label: 'Crit Damage', value: UnitStat.UNITSTATCRITICALDAMAGE, key: 'critdamage', short: 'CD', },
                {label: 'Crit Chance', value: UnitStat.UNITSTATATTACKCRITICALRATING, key: 'critchance', short: 'CC', },
                {label: 'Special Crit Chance', value: UnitStat.UNITSTATABILITYCRITICALRATING, key: 'critchance', short: 'SCC', },
                {label: 'Tenacity', value: UnitStat.UNITSTATRESISTANCE, key: 'tenacity', short: 'Ten', },
                {label: 'Potency', value: UnitStat.UNITSTATACCURACY, key: 'potency', short: 'Pot', },
                {label: 'Power', value: 'power', key: 'power', short: 'Power'},
            ],
            saved: false,
            error: null,
        }
    },
    watch: {
        selected(char) {
            Object.keys(this.related).forEach(key => this.$delete(this.related, key));

            if (this.selectedStats) {
                const usedStats = Object.keys(this.selectedStats);
                if (usedStats && usedStats.length) {
                    usedStats.forEach(statKey => this.$set(this.related, statKey, Object.keys(this.selectedStats[statKey].related || [])[0]));
                    this.openCard = usedStats[0];
                }
            }

            this.newStat = this.unusedStats[0];
        }
    },
    computed: {
        members() {
            return [this.squad.leader_id, ...this.squad.additional_members];
        },
        selectedStats() {
            return this.mutableStats[this.selected] || {};
        },
        unusedStats() {
            return this.stats.filter(stat => !this.selectedStats[stat.value]);
        },
        sortedStats() {
            return this.stats
                .filter(s => !!this.selectedStats[s.value]);
        },
        hasStatsDefined() {
            return !Array.isArray(this.mutableStats) && !!Object.keys(this.mutableStats).length;
        }
    },
    methods: {
        addStat() {
            if (!this.mutableStats[this.selected] || Array.isArray(this.mutableStats[this.selected])) {
                this.$set(this.mutableStats, this.selected, {});
            }
            this.$set(this.mutableStats[this.selected], this.newStat.value, {
                tier: [],
                related: {},
            });
            this.openCard = this.newStat.value;
            this.newStat = this.unusedStats[0];
        },
        showRelated(stat, char_id) {
            this.$set(this.related, stat.value, char_id);
        },
        hasRelated(statKey) {
            return !!this.related[statKey];
        },
        relatedPlaceholder(stat) {
            return `Ex: (${acronymize(this.units[this.related[stat.value]].name)} + 40) / .95 = ${acronymize(this.units[this.selected].name)} + 40`;
        },
        iconForStatKey(key) {
            return this.stats.find(s => s.value == key).key;
        },
        async save() {
            this.saved = false;
            this.error = null;
            let cleanStats = {};
            for (const char in this.mutableStats) {
                for (const stat in this.mutableStats[char]) {
                    const statGroup = this.mutableStats[char][stat];
                    const hasTier = statGroup.tier.length;
                    let cleanRelated = {};
                    for (const relation in statGroup.related) {
                        if (statGroup.related[relation].length) {
                            let formula = statGroup.related[relation]
                                .replace(acronymize(this.units[relation].name), relation)
                                .replace(acronymize(this.units[char].name), char);
                            cleanRelated[relation] = formula;
                        }
                    }

                    const hasRelations = Object.keys(cleanRelated).length;

                    if (hasTier || hasRelations) {
                        if (!cleanStats[char]) {
                            cleanStats[char] = {};
                        }
                        cleanStats[char][stat] = {
                            tier: statGroup.tier,
                            related: cleanRelated,
                        };
                    }
                }
            }

            try {
                const result = await axios.put(`/squad/${this.squad.id}/stats`, {
                    stats: cleanStats,
                });
                this.saved = true;
                setTimeout(() => this.showing = false, 2000);
            } catch (e) {
                this.error = e;
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    @import "../../sass/_variables";
    h3, div {
        color: $text;
        white-space: normal;
    }

    .tab-column, .content-column {
        max-height: 50vh;
        overflow-y: scroll;
    }

    .tab-column {
        padding: 0;
        border-right: 8px solid rgba($color: $overlay-navy, $alpha: 0.6);
    }
    .content-column {
        border-color: rgba($color: $overlay-navy, $alpha: 0.6);
        border-style: solid;
        border-top-width: 1px;
        border-bottom-width: 1px;
        padding: 4px 8px;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;

        > * {
            margin-bottom: 8px;
        }
    }

    .input-group-prepend.input-group-prepend {
        .input-group-text {

            width: 70px;
        }
    }

    .char-wrapper {
        // text-shadow: 1px 1px 0 #212529;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;

        &.glass-back .char-name {
            color: white;
        }

        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .stat-target-container {
        border: 1px solid $gray-400;
        border-radius: 8px;

        .stat-wrapper {
            padding: 8px;
        }
    }

    .white-text {
        color: $white;
    }

    .tabs {
        > div {
            padding: 2px 6px;
            margin-bottom: -1px;

            border-top-right-radius: 8px;
            border-top-left-radius: 8px;
            border: 1px solid transparent;

            &.selected {
                border-color: $gray-400;
                border-bottom-color: $white;
                overflow: hidden;
            }
        }
    }

    .related {
        position: relative;

        &::after {
            content: '';
            position: absolute;
            top: 0px;
            left: 4px;
            border: 4px solid $swgoh-orange;
            border-bottom-color: transparent;
            height: 11px;
        }
    }

</style>

<style lang="scss">
    @import "../../sass/_variables";
.stat-slash > span {
    color: $sw-yellow;
    margin-left: 4px;
}
</style>