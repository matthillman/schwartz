<template>
<div>
    <slot v-if="sortedMembers.length"></slot>
    <div class="set-filter row">
        <span class="sort-label">Sort by:</span>
        <div>
            <div class="btn" v-for="stat in stats" :key="stat.value" :class="{selected: sorted && stat && sorted.value == stat.value}" @click="sorted = stat">
                {{ stat.label }}
            </div>
        </div>
    </div>
    <div class="member-list">
        <table class="sortable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th v-for="unit in unitData" :key="unit.base_id"
                        @click="sortBy(unit.base_id)"
                        class="clickable"
                        :class="{sorted: sortCharacter === unit.base_id, reverse: sortCharacter === unit.base_id && reversed}"
                    ><span>{{ unit.name }}</span></th>
                </tr>
            </thead>
            <tbody>

            <tr v-for="member in sortedMembers" :key="member.ally_code">
                <td>
                    <a :href="`https://swgoh.gg${member.url}}`" target="_gg">
                        {{ member.player }}
                    </a>
                    <div class="small-note">Power: {{ member.characters.filter(c => baseIDs.includes(c.unit_name)).reduce((t, c) => t + c.power, 0) }}</div>
                </td>
                <td v-for="unit in unitData" :key="unit.base_id">
                    <div class="team-set">
                        <character :character="member.characters.filter(c => c.unit_name === unit.base_id)[0]" :keyStat="sorted"></character>
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
            units: String,
            members: String,
        },
        components: {
            'character': require('./Character.vue').default
        },
        computed: {
            unitData: function() { return JSON.parse(this.units); },
            memberData: function() { return JSON.parse(this.members); },
            baseIDs: function() { return Object.values(this.unitData).map(u => u.base_id); },
        },
        data: function() {
            return {
                stats: [
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
                ],
                sorted: {value: null},
                sortCharacter: null,
                reversed: false,
                sortedMembers: [],
            };
        },
        mounted: function() {
            this.sortCharacter = this.unitData[0].base_id;
            this.sorted = this.stats[0];
        },
        methods: {
            sortBy: function(base_id) {
            	this.reversed = this.sortCharacter === base_id && !this.reversed;
            	this.sortCharacter = base_id;
            },
            sortMembers: function() {
                let sorted = this.memberData.sort((a, b) => {
                    let charA = a.characters.find(c => c.unit_name === this.sortCharacter);
                    let charB = b.characters.find(c => c.unit_name === this.sortCharacter);

                    let charAVal = charA ? charA.stats.final[this.sorted.value] : 0;
                    let charBVal = charB ? charB.stats.final[this.sorted.value] : 0;

                    return charAVal - charBVal;
               });
               if (!this.reversed) {
                   sorted.reverse();
               }
               this.sortedMembers = sorted;
            },
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

.sort-label {
    margin-right: 8px;
}

.set-filter {
    justify-content: space-between;
}
</style>

