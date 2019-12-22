<template>
    <div class="mod">
        <div class="info">
            <div class="description" :class="{'mini': mini }">
                <span class="pips">
                    <span class="pip" v-for="n in mod.pips" :key="n"></span>
                </span>
                <div class="mod-image" :class="[mod.set, mod.slot, `tier-${mod.tier}`, {'gold': mod.pips > 5, 'mini': mini}]"></div>
                <span class="level" :class="[{max: mod.level == 15}, `tier-${mod.tier}`]">{{ mod.level }}</span>
            </div>
            <div class="data">
                <span class="primary">{{ translateValue(mod.primary.type, mod.primary.value, true) }} {{ translate(mod.primary.type, true) }}</span>
                <span class="secondary"
                    v-for="(value, type) in mod.secondaries"
                    :key="type"
                    :class="{good: isStatGood(value, type)}"
                    :type="translate(type)"
                >{{ translateValue(type, value) }}{{ padType(type) }}</span>
            </div>
        </div>
        <div v-if="!mini" class="mod-character">{{ mod.location }}</div>
    </div>
</template>

<script>
    export default {
        props: {
            mod: Object,
            mini: {
                type: Boolean,
                default: false
            },
        },
        methods: {
            padType(type) {
                const t = this.translate(type);
                return (t || '').indexOf('%') > -1 ? t : ` ${t}`;
            },
            isStatGood(value, type) {
                const t = this.translate(type);
                return (t == "offense" && +value >= 50)
                    || (t == "health" && +value >= 1000)
                    || (t == "defense" && +value >= 20)
                    || (t == "speed" && +value >= 15)
                ;
            },

            translate: function(stat, primary) {
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
            },
            translateValue: function(stat, value, primary) {
                switch(stat) {
                    case 'UNITSTATACCURACY':
                    case 'UNITSTATRESISTANCE':
                    case 'UNITSTATCRITICALCHANCEPERCENTADDITIVE':
                    case 'UNITSTATCRITICALDAMAGE':
                    case 'UNITSTATCRITICALNEGATECHANCEPERCENTADDITIVE':
                    case 'UNITSTATEVASIONNEGATEPERCENTADDITIVE':
                    case 'UNITSTATOFFENSEPERCENTADDITIVE':
                    case 'UNITSTATDEFENSEPERCENTADDITIVE':
                    case 'UNITSTATMAXHEALTHPERCENTADDITIVE':
                        return `${value}%`;
                }
                if (primary) {
                    switch(stat) {
                        case 'UNITSTATOFFENSEPERCENTADDITIVE':
                        case 'UNITSTATDEFENSEPERCENTADDITIVE':
                        case 'UNITSTATMAXHEALTHPERCENTADDITIVE':
                            return `${value}%`;
                    }
                }

                return value;
            },
        },
    }
</script>
