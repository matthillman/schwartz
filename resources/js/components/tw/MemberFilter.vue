<template>
    <div>
        <div class="input-group row no-margin justify-content-center align-items-end member-filter-row">
            <div class="column">
                <div class="small-note">Search by</div>
                <v-select
                    :options="options()"
                    :label="'name'"
                    :value="'base_id'"
                    v-model="unit"
                    class="unit-select"
                >
                    <template v-slot:option="unit">
                        <mini-squad-table v-if="unit.base_id == 'squad'" :squad="squad" :units="units" :max-units="5" no-header flex-width></mini-squad-table>
                        <unit-preview v-else :unit="unit"></unit-preview>
                    </template>
                    <template v-slot:selected-option="unit">
                        <mini-squad-table v-if="unit.base_id == 'squad'" :squad="squad" :units="units" :max-units="5" no-header flex-width></mini-squad-table>
                        <unit-preview v-else :unit="unit"></unit-preview>
                    </template>
                </v-select>
            </div>

            <div class="column">
                <div class="small-note">Sort by</div>
                <v-select :options="stats" :label="'label'" :value="'value'" v-model="sortStat"></v-select>
            </div>

            <div class="column">
                <div class="small-note">Limit to</div>
                <input class="form-control" type="number" min="1" max="25" step="1" width="6" v-model="limit">
            </div>

            <button class="btn btn-primary striped" @click="sortList"><span>Find</span></button>

        </div>

        <div v-show-slide="results.length" class="results-wrapper">
            <div>Total found: {{ results.length }}</div>
            <div class="table-wrapper">
                <table class="squad-table fixed micro" v-for="member of results" :key="member.ally_code">
                    <tbody>
                        <tr class="squad-row player-info">
                            <td>
                                <div class="column justify-content-center align-items-center">
                                    <input type="checkbox" v-model="checkedMembers" :value="member.ally_code">
                                </div>
                            </td>
                            <td>
                                <div class="name-wrapper">
                                    <div class="column">
                                        <div>{{ member.player }}</div>
                                        <div class="small-note">
                                            <span>{{ statLabel() }}:</span> <span>{{ statValue(member).toLocaleString() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td v-for="base_id in [squad.leader_id, ...squad.additional_members.slice(0, 4)]" :key="base_id">
                                <div v-if="member.characters.find(mc => mc.unit_name == base_id)">
                                    <character
                                        :character="member.characters.find(mc => mc.unit_name == base_id)"
                                        no-stats no-mods no-zetas
                                        :classes="'mini'"
                                    ></character>
                                </div>
                            </td>
                            <td v-if="squad.additional_members.length > 4">
                                <div class="column justify-content-center align-items-center extra-units">
                                    <tooltip>
                                        +{{ squad.additional_members.length - 4 }}
                                        <template #tooltip>
                                            <table class="squad-table micro">
                                                <tbody>
                                                    <tr class="squad-row tooltip-row">
                                                        <td v-for="char_id in squad.additional_members.slice(4)" :key="char_id">
                                                            <div>
                                                            <character
                                                                :character="member.characters.find(mc => mc.unit_name == char_id)"
                                                                no-stats no-mods no-zetas
                                                                :classes="'mini'"
                                                            ></character>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </template>
                                    </tooltip>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import { UnitStat } from '../../util/swgoh-enums';

export default {
    components: {
        'unit-preview': require('../util/UnitPreview.vue').default,
    },
    props: {
        members: Array,
        squad: Object,
        units: Object,
        max: {
            type: Number,
            default: 25,
        }
    },
    data() {
        return {
            stats: [
                {label: 'Power', value: 'power', key: 'power', short: 'Power'},
                {label: 'Speed', value: UnitStat.UNITSTATSPEED, key: 'speed', short: 'Speed', },
                {label: 'Offense', value: UnitStat.UNITSTATATTACKDAMAGE, key: 'offense', short: 'Off', },
                {label: 'Special Offense', value: UnitStat.UNITSTATABILITYPOWER, key: 'offense', short: 'S Off', },
                {label: 'Crit Damage', value: UnitStat.UNITSTATCRITICALDAMAGE, key: 'critdamage', short: 'CD', },
                {label: 'Crit Chance', value: UnitStat.UNITSTATATTACKCRITICALRATING, key: 'critchance', short: 'CC', },
                {label: 'Special Crit Chance', value: UnitStat.UNITSTATABILITYCRITICALRATING, key: 'critchance', short: 'SCC', },
                {label: 'Health', value: UnitStat.UNITSTATMAXHEALTH, key: 'health', short: 'Health', },
                {label: 'Protection', value: UnitStat.UNITSTATMAXSHIELD, key: 'health', short: 'Prot', },
                {label: 'Tenacity', value: UnitStat.UNITSTATRESISTANCE, key: 'tenacity', short: 'Ten', },
                {label: 'Potency', value: UnitStat.UNITSTATACCURACY, key: 'potency', short: 'Pot', },
            ],
            unit: {base_id: 'squad', name: 'Squad'},
            sortStat: {label: 'Power', value: 'power', key: 'power', short: 'Power'},
            limit: this.max,
            results: [],
            checkedMembers: [],
        };
    },
    watch: {
        checkedMembers() {
            this.$emit('changed', this.checkedMembers);
        }
    },
    methods: {
        options() {
            return [{base_id: 'squad', name: 'Squad'}].concat([this.squad.leader_id, ...this.squad.additional_members].map(base_id => this.units[base_id]));
        },
        sortList() {
            const units = this.unit.base_id === 'squad' ? [this.squad.leader_id, ...this.squad.additional_members] : [this.unit.base_id];

            this.results = this.members
                .sort((a, b) => {
                    let charsA = a.characters.filter(c => units.includes(c.unit_name));
                    let charsB = b.characters.filter(c => units.includes(c.unit_name));

                    let AVal = charsA.reduce((t, c) => t + (this.sortStat.value == 'power' ? c.power : c.stats.final[this.sortStat.value]), 0);
                    let BVal = charsB.reduce((t, c) => t + (this.sortStat.value == 'power' ? c.power : c.stats.final[this.sortStat.value]), 0);

                    return AVal - BVal;
                })
                .reverse()
                .slice(0, this.limit);
            this.checkedMembers = this.results.map(m => m.ally_code);
        },
        statLabel() {
            if (this.unit.base_id === 'squad') {
                return `Total ${this.sortStat.short}`;
            } else {
                return `${this.unit.name} ${this.sortStat.short}`
            }
        },
        statValue(member) {
            const units = this.unit.base_id === 'squad' ? [this.squad.leader_id, ...this.squad.additional_members] : [this.unit.base_id];
            let chars = member.characters.filter(c => units.includes(c.unit_name));
            return chars.reduce((t, c) => t + (this.sortStat.value == 'power' ? c.power : c.stats.final[this.sortStat.value]), 0)
        },
    }
}
</script>

<style lang="scss">
.member-filter-row {
    .unit-select.v-select, .v-select {
        width: 260px;
        .vs__dropdown-toggle {
            height: 42px;
        }
    }

    > div.column > input, > button {
        height: 42px;
    }
}

.input-group {
    > * + * {
        margin-left: 4px;
    }
}

.results-wrapper {
    width: 500px;
    margin: 8px auto;
    max-height: 300px;

    > div {
        height: 20px;
    }

    .table-wrapper {
        height: calc(100% - 20px);
        max-height: 280px;
        overflow-y: scroll;
        scroll-snap-type: y mandatory;

        > table {
            scroll-snap-align: start;
        }
    }

   .player-info {
       > td:nth-child(1) {
           width: 40px;
       }
       > td:nth-child(2) {
            width: 240px;

            > div > div {
                padding: 2px 4px;
            }
        }
    }
}
</style>
