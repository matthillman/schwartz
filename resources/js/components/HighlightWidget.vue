<template>
    <div class="set-filter row">
        <span class="sort-label note">Highlight based on:</span>
        <div>
            <div class="btn" v-for="stat in stats" :key="stat" :class="{selected: selected && stat && selected == stat}" @click="updateSelected(stat)">
                {{ labelFor(stat) }}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['starting'],
        data: function() {
            return {
                stats: [
                    'stars',
                    'power',
                    'power-plus',
                    'power-stars',
                    'gear',
                    'mods',
                ],
                selected: '',
            }
        },
        mounted: function() {
            this.selected = this.starting;
        },
        methods: {
            updateSelected(stat) {
                this.$root.highlight = stat;
                this.selected = stat;
            },
            labelFor(stat) {
                switch (stat) {
                    case 'stars': return '★';
                    case 'power-stars': return 'power/★';
                    case 'power-plus': return 'power+';
                    default: return stat;
                }
            }
        },
    }
</script>

<style lang="scss" scoped>
.sort-label {
    margin-right: 4px;
}

.btn {
    padding: 2px .75rem;
    cursor: pointer;
}

.row {
    margin-right: 0;
    margin-left: 0;
}
</style>