<template>
  <div class="characters">
    <span
        :gear="character.gear_level"
        :stars="character.rarity"
        :power="character.highlight_power"
        :mod-grade="statGrade"
        class="character"
        :class="[character.alignment, {clickable: showMods, ultimate: character.has_ultimate_ability}]"
        @click="onPopoverOpen"
    >
      <div
        class="portrait"
        :class="[{ ship: character.is_ship, 'capital': character.is_capital_ship }, classes]"
      >
        <div v-if="character.is_ship" class="ship-wrapper">
          <img class="character" :src="`/images/units/${ character.unit_name }.png`">
        </div>
        <div
          v-if="character.is_ship"
          class="gear"
          :class="[`g${ character.gear_level }`]"
          :style="`--gear-image: url('/images/units/gear/${ character.is_capital_ship ? 'capital-' : ''}ship-frame.svg')`"
        ></div>

        <img
          v-if="!character.is_ship"
          class="character round"
          :src="`/images/units/${ character.unit_name }.png`"
        >
        <div
          v-if="!character.is_ship"
          class="gear"
          :class="[`g${ character.gear_level }`]"
          :style="`--gear-image: url('/images/units/gear/gear-icon-g${ character.gear_level }.png')`"
        ></div>

        <div class="stars">
          <img
            v-for="index in range(7, 1)"
            :key="index"
            :class="{ full : character.rarity >= index, empty: character.rarity < index }"
            :src="`/images/units/stars/${character.rarity >= index ? 'active' : 'inactive'}.png`"
          >
        </div>

        <div v-if="character.zetas && character.zetas.length" class="zetas">
          <img src="/images/units/abilities/zeta.png">
          <span class="value">{{ character.zetas.length }}</span>
        </div>

        <div v-if="character.relic > 1 && character.gear_level > 12" class="relic">
          <span class="value">{{ character.relic - 2 }}</span>
        </div>

        <div class="level">
          <span class="value">{{ character.level }}</span>
        </div>
      </div>
      <span class="zeta-list" v-if="showZetas">
        <span v-for="zeta in character.zetas" :key="zeta.id" class="zeta">{{ zeta.class[0] }}</span>
      </span>
      <div class="stat-container" v-if="showStats">
        <div v-for="(stat, key) in character.key_stats" :key="key" class="stat-wrapper">
          <span class="stat" :grade="character.stat_grade[key]">
            <span>{{ formatStat(stat[1], key).toLocaleString() }}</span>
            <span class="mod-set-image tier-5 mini" :class="[stat[0]]"></span>
          </span>
        </div>
      </div>
      <div
        class="stat-container"
        v-if="showStats && !Object.keys(character.key_stats).map(k => +k).includes(keyStat.value)"
      >
        <div class="stat-wrapper">
          <span class="stat">
            <span>{{ formatStat(keyStat.key === 'power' ? character.power : character.stats.final[keyStat.value], keyStat.value).toLocaleString() }}</span>
            <ion-icon v-if="keyStat.key === 'power'" name="flash" size="micro"></ion-icon>
            <span v-else class="mod-set-image tier-5 mini" :class="[keyStat.key]"></span>
          </span>
        </div>
      </div>

        <div v-if="showName" class="name-wrapper">
          {{ character.display_name }}
        </div>
    </span>

    <modal v-if="modalMods" @close="modalMods = false">
        <h3 slot="header">{{ member.player }}: {{ character.display_name }}</h3>
        <div slot="body" class="mod-details" :class="{'justify-content-center': fetchingMods}">
            <loading-indicator v-if="fetchingMods"></loading-indicator>
            <div
                v-else-if="character.mods && character.mods.length"
                v-for="shape in ['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross']"
                :key="shape"
            >
                <mod :mod="modFor(shape)" v-if="modFor(shape)"></mod>
                <div v-else class="mod missing">No {{ shape }} equipped</div>
            </div>
            <div v-else>No mods found</div>
        </div>
        <div slot="footer" class="bonuses" v-if="character.mods && character.mods.length">
            <div
                v-for="attribute in attributes"
                :key="attribute"
            >{{ translate(attribute) }}: {{ statTotalFor(attribute) }}</div>
        </div>
    </modal>
  </div>
</template>

<script>
import { UnitStat } from "../util/swgoh-enums";

const percent_stats = [
  'UNITSTATCRITICALDAMAGE',
  'UNITSTATATTACKCRITICALRATING',
  'UNITSTATABILITYCRITICALRATING',
  'UNITSTATRESISTANCE',
  'UNITSTATACCURACY',
  'UNITSTATCRITICALCHANCEPERCENTADDITIVE',
  'UNITSTATHEALTHSTEAL',
  'UNITSTATSHIELDPENETRATION',
  'UNITSTATDODGENEGATERATING',
  'UNITSTATARMOR',
  'UNITSTATDODGERATING',
  'UNITSTATATTACKCRITICALNEGATERATING',
  'UNITSTATDEFLECTIONNEGATERATING',
  'UNITSTATSUPPRESSION',
  'UNITSTATDEFLECTIONRATING',
  'UNITSTATABILITYCRITICALNEGATERATING',
];

function getStatKey(value) {
  return Object.keys(UnitStat).find(key => UnitStat[key] == value);
}

function range(size, startAt = 0) {
  return [...Array(size).keys()].map(i => i + startAt);
}

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
        case 'UNITSTATCRITICALDAMAGE': return 'crit damage';
        case 'UNITSTATCRITICALCHANCEPERCENTADDITIVE': return 'crit chance';
        case 'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE': return 'crit avoidance';
        case 'UNITSTATEVASIONNEGATEPERCENTADDITIVE': return 'accuracy';

        case 'UNITSTATCRITICALCHANCE': return 'crit chance';
        case 'UNITSTATCRITICALNEGATECHANCE': return 'crit avoidance';
        case 'UNITSTATEVASIONNEGATE': return 'accuracy';
    }
    return stat;
}

export default {
  props: {
    character: Object,
    member: Object,
    keyStat: {
      type: Object,
      default: function() {
        return { value: 5 };
      }
    },
    noStats: {
      type: Boolean,
      default: false
    },
    noMods: {
      type: Boolean,
      default: false
    },
    noZetas: {
      type: Boolean,
      default: false
    },
    showName: {
      type: Boolean,
      default: false
    },
    classes: {
      type: String,
      default: '',
    }
  },
  data: function() {
    return {
        fetchingMods: false,
        modalMods: false,
        attributes: ["UNITSTATSPEED", "UNITSTATOFFENSE", "UNITSTATDEFENSE", "UNITSTATMAXHEALTH", "UNITSTATMAXSHIELD", "UNITSTATCRITICALCHANCEPERCENTADDITIVE", "UNITSTATRESISTANCE"],
    };
  },
  computed: {
    statGrade: function() {
      if (!this.character.stat_grade) { return null; }

      let statValues = Object.values(this.character.stat_grade);
      return statValues.length
        ? statValues.reduce((c, v) => Math.min(c, v))
        : null;
    },
    showStats: function() {
      return !this.noStats;
    },
    showMods: function() {
      return !this.noMods;
    },
    showZetas: function() {
      return !this.noZetas;
    },
  },
  methods: {
    range,
    translate,
    formatStat: function(value, stat) {
      if (stat === 'power') {
        return value;
      }

      const statKey = getStatKey(stat);

      if (!statKey) {
        return value;
      }

      if ([ 'UNITSTATATTACKCRITICALRATING', 'UNITSTATABILITYCRITICALRATING' ].includes(statKey)) {
        value = value / 100;
      }

      return percent_stats.includes(statKey) ? `${parseFloat(value).toFixed(1)}%` : parseInt(value);
    },
    onPopoverOpen: function() {
      if (this.noMods) { return; }

      this.modalMods = true;

      if (this.character.mods && this.character.mods.length) { return; }

      this.fetchingMods = true;

      axios
        .get(`/character_mods/${this.character.id}`)
        .then(res => {
          this.character.mods = res.data;
        })
        .finally(() => (this.fetchingMods = false));
    },
    modFor(shape) {
      return this.character.mods.find(mod => mod.slot === shape);
    },
    statTotalFor: function(attribute) {
        attribute = attribute || "UNITSTATSPEED";
        let total = 0;
        let shapes = ["square", "diamond", "triangle", "circle", "cross"];

        shapes.forEach((shape) => {
            let mod = this.modFor(shape);
            if (!mod) { return; }
            total += parseFloat(mod.secondaries[attribute], 10) || 0;
        });

        let arrow = this.modFor('arrow');
        if (arrow && arrow.primary.type == attribute && attribute == "UNITSTATSPEED") {
            total += +arrow.primary.value
        } else if (arrow) {
            total += parseFloat(arrow.secondaries[attribute]) || 0;
        }

        total = Math.round(total * 100) / 100;

        const hasSpeedBonus = this.character.mods.reduce((s, mod) => s + (mod.set === 'speed'), 0) >= 4;

        return total + (attribute == "UNITSTATSPEED" && hasSpeedBonus ? " (+10%)" : "");
    },
  }
};
</script>

<style lang="scss" scoped>
@import "../../sass/_variables";

.clickable {
  cursor: pointer;
}

.characters {
    height: 100%;
}

[name="flash"] {
    color: $sw-yellow;
}

.name-wrapper {
  font-weight: 700;
  text-align: center;
  line-height: 1;
  margin-top: 4px;
}

</style>