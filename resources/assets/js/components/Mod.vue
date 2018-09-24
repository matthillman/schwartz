<template>
    <div class="mod">
        <div class="info">
            <div class="description">
                <span class="pips">
                    <span class="pip" v-for="n in mod.pips" :key="n"></span>
                </span>
                <div class="mod-image" :class="[mod.set, mod.slot, `tier-${mod.tier}`, {'gold': mod.pips > 5}]"></div>
                <span class="level" :class="[{max: mod.level == 15}, `tier-${mod.tier}`]">{{ mod.level }}</span>
            </div>
            <div class="data">
                <span class="primary">{{ mod.primary.value }} {{ mod.primary.type }}</span>
                <span class="secondary"
                    v-for="(value, type) in mod.secondaries"
                    :key="type"
                    :class="{good: isStatGood(value, type)}"
                    :type="type"
                >{{ value }} {{ type }}</span>
            </div>
        </div>
        <div class="character">{{ mod.location }}</div>
    </div>
</template>

<script>
    export default {
        props: ['mod'],
        methods: {
            isStatGood(value, type) {
                return (type == "offense" && +value >= 50)
                    || (type == "health" && +value >= 1000)
                    || (type == "defense" && +value >= 20)
                    || (type == "speed" && +value >= 15)
                ;
            }
        },
    }
</script>
