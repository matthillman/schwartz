<template>
    <div :class="{'card-body': cardBody}">
        <div v-if="hasSlot('top-trigger')" class="trigger-wrapper" @click="open = !open">
            <slot name="top-trigger" v-bind:open="open"></slot>
        </div>
        <div
            v-show-slide="open"
            @slide-open-end="slideOpenEnd"
            @slide-close-start="slideCloseStart"
        >
            <slot>Body</slot>
        </div>
        <div v-if="hasSlot('trigger')" class="trigger-wrapper" @click="open = !open">
            <slot name="trigger" v-bind:open="open"></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        startOpen: Boolean,
        cardBody: Boolean,
    },
    mounted() {
        this.open = this.startOpen;
    },
    data() {
        return {
            open: false,
        }
    },
    methods: {
        hasSlot(name = 'default') {
            return !!this.$slots[ name ] || !!this.$scopedSlots[ name ];
        },
        slideOpenEnd(el) {
            el.target.style.overflow = 'visible';
        },
        slideCloseStart(el) {
            el.target.style.overflow = 'hidden';
        },
    }
}
</script>

<style lang="scss" scoped>
.trigger-wrapper {
    cursor: pointer;
}
</style>
