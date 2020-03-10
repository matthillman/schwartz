<template>
    <div class="page-wrapper">
        <div class="page-container" :style="{ transform: `translateX(${offset}px)` }">
            <div class="card" v-for="page in pageRange()" :key="page">
                <slot name="page" v-bind:page="page">{{ page }}</slot>
            </div>
        </div>
    </div>
</template>

<script>
function range(size, startAt = 0) {
  return [...Array(size).keys()].map(i => i + startAt);
}

export default {
    props: {
        firstPage: {
            type: Number,
            default: 1,
        },
        currentPage: {
            type: Number,
            default: 1,
        },
        lastPage: Number,
    },
    data() {
        return {
            offset: 0,
        };
    },
    watch: {
        currentPage() {
            this.offset = -1 * 460 * (this.currentPage - this.firstPage);
        },
    },
    methods: {
        pageRange() {
            return range(this.lastPage - this.firstPage + 1, this.firstPage);
        },
    },
}
</script>

<style lang="scss" scoped>
@import "../../../sass/_variables.scss";
.page-wrapper {
    overflow-x: hidden;
    overflow-y: visible;
}

.page-container {
    display: flex;
    justify-content: flex-start;

    margin-top: 8px;
    padding-bottom: 8px;

    transition: transform 150ms ease-out;
    transform: translateX(0px);

    .card {
        $card-width: 450px;
        width: $card-width;
        min-width: $card-width;
        max-width: $card-width;
        margin: 0 5px;
        border: 1px solid $gray-600;
        border-radius: 8px;
        box-shadow: 1px 1px 3px $gray-800;

        &:first-of-type {
            margin-left: 0;
        }
        &:last-of-type {
            margin-right: 0;
        }
    }
}
</style>