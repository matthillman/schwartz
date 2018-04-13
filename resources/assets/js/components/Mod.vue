<template>
    <div class="mod">
        <div class="info">
            <div class="description">
                <span class="pips">
                    <span class="pip" v-for="n in mod.pips" :key="n"></span>
                </span>
                <img class="image" :src="imgSrcFor(mod.set, mod.slot)" width="46">
                <span class="level" :class="{max: mod.level == 15}">{{ mod.level }}</span>
            </div>
            <div class="data">
                <span class="primary">{{ mod.primary.value }} {{ mod.primary.type }}</span>
                <span class="secondary" v-for="(value, type) in mod.secondaries" :key="type">{{ value }} {{ type }}</span>
            </div>
        </div>
        <div class="character">{{ mod.location }}</div>
    </div>
</template>

<script>
    export default {
        props: ['mod'],
        methods: {
            imgSrcFor: function(set, slot) {
                return "/images/mods/" + slot + "_" + set + ".png";
            },
        },
    }
</script>
