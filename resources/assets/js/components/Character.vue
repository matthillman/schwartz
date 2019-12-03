<template>
<popover class="characters" :name="`character-${character.id}`">
    <div slot="face">
        <span :gear="character.gear_level"
            :stars="character.rarity"
            :power="character.highlight_power"
            :mod-grade="statGrade"
            class="character"
            :class="[character.alignment]"
        >
            <div class="portrait" :class="{ ship: character.is_ship, 'capital': character.is_capital_ship }">
                <div v-if="character.is_ship" class="ship-wrapper">
                    <img class="character" :src="`/images/units/${ character.unit_name }.png`">
                </div>
                <div v-if="character.is_ship" class="gear" :class="[`g${ character.gear_level }`]" :style="`--gear-image: url('/images/units/gear/${ character.is_capital_ship ? 'capital-' : ''}ship-frame.svg')`"></div>

                <img v-if="!character.is_ship"  class="character round" :src="`/images/units/${ character.unit_name }.png`">
                <div v-if="!character.is_ship" class="gear" :class="[`g${ character.gear_level }`]" :style="`--gear-image: url('/images/units/gear/gear-icon-g${ character.gear_level }.png')`"></div>

                <div class="stars">
                    <img v-for="index in range(7, 1)" :key="index"
                        :class="{ full : character.rarity >= index, empty: character.rarity < index }"
                        :src="`/images/units/stars/${character.rarity >= index ? 'active' : 'inactive'}.png`"
                    >
                </div>

                <div v-if="character.zetas && character.zetas.length" class="zetas">
                    <img src="/images/units/abilities/zeta.png">
                    <span class="value">{{ character.zetas.length }}</span>
                </div>

                <div v-if="character.relic > 1" class="relic">
                    <span class="value">{{ character.relic - 2 }}</span>
                </div>

                <div class="level">
                    <span class="value">{{ character.level }}</span>
                </div>

            </div>
            <span class="zeta-list">
                <span v-for="zeta in character.zetas" :key="zeta.id" class="zeta">{{ zeta.class[0] }}</span>
            </span>
            <div class="stat-container" v-if="!noStats">
                <div v-for="(stat, key) in character.key_stats" :key="key" class="stat-wrapper"><span class="stat" :grade="character.stat_grade[key]"><span>{{ stat[1] }}</span><span class="mod-set-image tier-5 mini" :class="[stat[0]]"></span></span></div>
            </div>
            <div class="stat-container" v-if="!noStats && !Object.keys(character.key_stats).map(k => +k).includes(keyStat.value)">
                <div class="stat-wrapper"><span class="stat"><span>{{ formatStat(character.stats.final[keyStat.value]) }}</span><span class="mod-set-image tier-5 mini" :class="[keyStat.key]"></span></span></div>
            </div>
        </span>
    </div>
</popover>
</template>

<script>

import { UnitStat } from '../util/swgoh-enums';

function range(size, startAt = 0) {
    return [...Array(size).keys()].map(i => i + startAt);
}

export default {
    props: {
        character: Object,
        keyStat: {
            type: Object,
            default: function() { return { value: 5 }; },
        },
        noStats: {
            type: Boolean,
            default: false,
        },
        noMods: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        statGrade: function() {
            let statValues = Object.values(this.character.stat_grade);
            return statValues.length ? statValues.reduce((c, v) => Math.min(c, v)) : null;
        },
    },
    methods: {
        range,
        formatStat: function(value) {
            if (/\./.test(`${value}`)) {
                return `${Math.round(value * 10000) / 100}%`;
            }

            return value;
        }
    }
}
</script>
