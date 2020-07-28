<template>
<div>
    <slot v-if="sortedMembers.length"></slot>
    <div class="row stat-filter no-margin justify-content-start align-items-center">
        <span class="sort-label">Sort by:</span>
        <v-select :options="stats" v-model="sorted" :clearable="false">
            <template v-slot:option="stat">
                <div class="row no-margin align-items-center">
                    <ion-icon v-if="stat.key === 'power'" name="flash" size="micro"></ion-icon>
                    <ion-icon v-else-if="stat.key === 'stats'" name="trophy" size="micro"></ion-icon>
                    <span v-else class="mod-set-image tier-5 mini" :class="stat.key"></span>
                    <span>{{ stat.label }}</span>
                </div>
            </template>
            <template v-slot:selected-option="stat">
                <div class="row no-margin align-items-center">
                    <ion-icon v-if="stat.key === 'power'" name="flash" size="micro"></ion-icon>
                    <ion-icon v-else-if="stat.key === 'stats'" name="trophy" size="micro"></ion-icon>
                    <span v-else class="mod-set-image tier-5 mini" :class="stat.key"></span>
                    <span>{{ stat.label }}</span>
                </div>
            </template>
        </v-select>
    </div>
    <div class="member-list">
        <table class="sortable">
            <thead>
                <tr>
                    <th class="header clickable"
                        @click="sortBy(null)"
                        :class="{sorted: sortCharacter === null, reverse: sortCharacter === null && reversed}"
                    ><span>Member</span></th>
                    <th v-for="unit in units" :key="unit.base_id"
                        @click="sortBy(unit.base_id)"
                        class="clickable"
                        :class="{sorted: sortCharacter === unit.base_id, reverse: sortCharacter === unit.base_id && reversed}"
                    ><span>{{ unit.name }}</span></th>
                </tr>
            </thead>
            <tbody>

            <tr v-for="member in sortedMembers" :key="member.ally_code">
                <td class="header">
                    <a :href="`/member/${member.ally_code}`">
                        <span>{{ member.player }}</span>
                    </a>
                    <div class="small-note">Power: {{ member.characters.filter(c => baseIDs.includes(c.unit_name)).reduce((t, c) => t + c.power, 0).toLocaleString() }}</div>
                    <div v-if="sorted.key === 'stats'" class="small-note">Max Grade: {{ memberGrade(member) }}</div>
                </td>
                <td v-for="unit in units" :key="unit.base_id">
                    <div class="team-set">
                        <character v-if="characterForMember(unit, member)" :character="characterForMember(unit, member)" :member="member" :keyStat="sorted"></character>
                        <span missing v-if="!characterForMember(unit, member)">None</span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</template>

<script>
    import { UnitStat } from '../util/swgoh-enums';

    export default {
        props: {
            units: Array,
            members: Array,
        },
        data: function() {
            return {
                stats: [
                    {label: 'Power', value: 'power', key: 'power'},
                    {label: 'Speed', value: UnitStat.UNITSTATSPEED, key: 'speed'},
                    {label: 'Offense', value: UnitStat.UNITSTATATTACKDAMAGE, key: 'offense'},
                    {label: 'Special Offense', value: UnitStat.UNITSTATABILITYPOWER, key: 'offense'},
                    {label: 'Crit Damage', value: UnitStat.UNITSTATCRITICALDAMAGE, key: 'critdamage'},
                    {label: 'Crit Chance', value: UnitStat.UNITSTATATTACKCRITICALRATING, key: 'critchance'},
                    {label: 'Special Crit Chance', value: UnitStat.UNITSTATABILITYCRITICALRATING, key: 'critchance'},
                    {label: 'Health', value: UnitStat.UNITSTATMAXHEALTH, key: 'health'},
                    {label: 'Protection', value: UnitStat.UNITSTATMAXSHIELD, key: 'health'},
                    {label: 'Tenacity', value: UnitStat.UNITSTATRESISTANCE, key: 'tenacity'},
                    {label: 'Potency', value: UnitStat.UNITSTATACCURACY, key: 'potency'},
                    {label: 'Armor', value: UnitStat.UNITSTATARMOR, key: 'defense', },
                    {label: 'Resistance', value: UnitStat.UNITSTATSUPPRESSION, key: 'defense', },
                    {label: 'Crit Avoidance', value: UnitStat.UNITSTATATTACKCRITICALNEGATEPERCENTADDITIVE, key: 'critavoid', },
                ],
                sorted: {label: 'Speed', value: UnitStat.UNITSTATSPEED, key: 'speed'},
                sortCharacter: null,
                reversed: false,
                sortedMembers: [],
                baseIDs: [],
            };
        },
        mounted: function() {
            this.baseIDs = Object.values(this.units).map(u => u.base_id);

            this.sortCharacter = this.units[0].base_id;
            this.sorted = this.stats[0];
        },
        watch: {
            '$root.highlight': function(val) {
                if (val === 'mods') {
                    this.stats.unshift({
                        label: 'Mod Tier',
                        value: 'stats',
                        key: 'stats'
                    });
                    this.sorted = this.stats[0];
                } else {
                    const stat = this.stats.find(s => s.value === 'stats');
                    this.stats.slice(this.stats.indexOf(stat), 0);
                }
            }
        },
        methods: {
            sortBy(base_id) {
            	this.reversed = this.sortCharacter === base_id && !this.reversed;
            	this.sortCharacter = base_id;
            },
            sortMembers() {
                const sortIDs = this.sortCharacter === null ? this.units.map(u => u.base_id) : [this.sortCharacter];
                const starting = this.sorted.value === 'stats' ? 10000 : 0;
                let sorted = this.members.sort((a, b) => {
                    const totals = sortIDs.reduce((total, base_id) => {
                        let charA = a.characters.find(c => c.unit_name === base_id);
                        let charAVal = charA ? (this.sorted.value === 'power' ? charA.power :
                            (this.sorted.value === 'stats' ? this.statGrade(charA) :
                                charA.stats.final[this.sorted.value]
                            )
                        ) : 0;

                        let charB = b.characters.find(c => c.unit_name === base_id);
                        let charBVal = charB ? (this.sorted.value === 'power' ? charB.power :
                            (this.sorted.value === 'stats' ? this.statGrade(charB) :
                                charB.stats.final[this.sorted.value]
                            )
                        ) : 0;

                        if (this.sorted.value === 'stats') {
                            return { a: Math.min(total.a, charAVal), b: Math.min(total.b, charBVal) };
                        }
                        return {a: total.a + charAVal, b: total.b + charBVal};
                    }, {a: starting, b: starting})

                    return totals.a - totals.b;
               });
               if (!this.reversed) {
                   sorted.reverse();
               }
               this.sortedMembers = sorted;
            },
            characterForMember(unit, member) {
                return member.characters.filter(c => c.unit_name === unit.base_id)[0]
            },
            statGrade(character) {
                if (!character.stat_grade) { return 0; }

                let statValues = Object.values(character.stat_grade).map(g => Number.isInteger(g) ? g : (g % 1).toFixed(1).substring(2));
                return statValues.length
                    ? statValues.reduce((c, v) => Math.min(c, v))
                    : 0;
            },
            memberGrade(member) {
                const grade = member.characters.filter(c => this.baseIDs.includes(c.unit_name)).map(this.statGrade).reduce((t, c) => Math.min(t, c), 10000);
                return [0, 0, 3, 2, 1][grade];
            }
        },
        created: function() {
            this.unwatchSort = this.$watch(() => `${this.sortCharacter}:${this.sorted.value}:${this.reversed}`, this.sortMembers);
        },
        beforeDestroy: function() {
            this.unwatchSort();
        },
    }
</script>

<style lang="scss" scoped>
@import "../../sass/_variables.scss";

.member-list {
    margin-top: 16px;

    table.sortable th:not(:first-of-type) span {
        justify-content: center;
    }
}

.clickable, .btn {
    cursor: pointer;

    &.clickable:hover {
        text-decoration: underline;
    }
}

[name="flash"], [name="trophy"] {
    color: $sw-yellow;
}

.stat-filter {
    margin: 8px 0 0 8px;
}

.sort-label {
    margin-right: 8px;
}

.v-select {
    flex: 0 1 300px;
}
</style>

