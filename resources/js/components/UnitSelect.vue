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
        required: Boolean
    },
    data() {
        return {
            units: [],
            lastSearchTerm: '',
            unit: null,
        };
    },
    computed: {
        needsSelection() {
            return !this.unit || (Array.isArray(this.unit) && !this.unit.length);
        }
    },
    watch: {
        unit() {
            console.warn(this.required && this.needsSelection, this.required, this.needsSelection);
        }
    },
    methods: {
        maybeDoSearch: _.debounce(async function(search, loading) {
            loading(true);
            this.lastSearchTerm = search;
            const response = await axios.get(`/unit-search?search=${search}`);

            this.units = response.data.data;

            loading(false);
        }, 250),
    }
}
</script>

<style lang="scss">
.unit-select {
    &.dropdown.v-select {
        min-width: 200px;

        .dropdown-toggle {
            height: 36px;
            padding: 0;
        }

        .vs__selected-options {
            padding: 0;

            .portrait-preview {
                height: 34px;
                max-height: 34px;
            }
        }

        &.single .selected-tag {
            margin: 0;
            padding: 0;
            border: none;
        }
    }
    .multiple &.dropdown.v-select {
        --select-width: calc(100% - 71px);
        width: var(--select-width);

        .dropdown-toggle {
            min-height: 42px;
            height: auto;
            padding: 0;
        }

        .selected-tag {
            margin: 2px 4px;
            padding-left: 0;
        }

        .character-name {
            text-align: left;
        }
    }
}
</style>