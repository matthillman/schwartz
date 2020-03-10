<template>
    <div class="portrait-preview">
        <img class="character" :src="`/images/units/${ unit.base_id }.png`" />
        <div class="character-name">
            {{ unit.name }}
        </div>
    </div>
</template>

<script>
export default {
    props: {
        unit: Object,
    },
}
</script>
