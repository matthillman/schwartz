<template>
    <v-select
        :options="units"
        :multiple="multiple"
        :placeholder="placeholder"
        :label="'name'"
        v-model="unit"
        @input="$emit('input', $event)"
        @search="maybeDoSearch"
        @search:focus="maybeDoSearch('', () => {})"
        :filterable="false"
        class="unit-select"
    >
        <template #search="{attributes, events}">
            <input
                class="vs__search"
                :required="required && needsSelection"
                v-bind="attributes"
                v-on="events"
            />
        </template>
        <template v-slot:option="unit">
            <unit-preview :unit="unit"></unit-preview>
        </template>
        <template v-slot:selected-option="unit">
            <unit-preview :unit="unit"></unit-preview>
        </template>
        <template v-slot:no-options>
            <div v-if="lastSearchTerm.length">No units found for {{ lastSearchTerm }}</div>
            <div v-else>Loading…</div>
        </template>
    </v-select>
</template>

<script>
export default {
    components: {
        'unit-preview': require('./UnitPreview.vue').default,
    },
    props: {
        placeholder: String,
        multiple: Boolean,
        required: Boolean,
        value: Object|Array,
    },
    data() {
        return {
            units: [],
            lastSearchTerm: '',
            unit: this.value,
            searchID: 0,
        };
    },
    computed: {
        needsSelection() {
            return !this.unit || (Array.isArray(this.unit) && !this.unit.length);
        }
    },
    methods: {
        maybeDoSearch: _.debounce(async function(search, loading) {
            loading(true);
            this.lastSearchTerm = search;
            const searchTime = (new Date).getTime();
            this.searchID = searchTime;
            const response = await axios.get(`/unit-search?search=${search}`);

            if (this.searchID === searchTime) {
                this.units = response.data.data;
                loading(false);
            }

        }, 400),
    }
}
</script>

<style lang="scss">
.unit-select {
    &.v-select {
        min-width: 200px;

        .vs__dropdown-toggle {
            height: 36px;
            padding: 0;
            background: #fff;
        }

        .vs__selected-options {
            padding: 0;

            .portrait-preview {
                height: 34px;
                max-height: 34px;
            }
        }

        &.vs--single .vs__selected {
            margin: 0;
            padding: 0;
            border: none;
        }
    }
    .multiple &.v-select {
        --select-width: calc(100% - 70px);
        width: var(--select-width);

        .vs__dropdown-toggle {
            min-height: 42px;
            height: auto;
            padding: 0;
        }

        .vs__selected {
            margin: 2px 4px;
            padding-left: 0;
        }

        .character-name {
            text-align: left;
        }
    }
}
</style>