<template>
    <label class="row no-margin align-items-end input-group" :class="{ 'no-clear': !clearable }">
        <slot></slot>
        <v-select
            :options="options"
            v-model="selected"
            :placeholder="placeholder"
            :label="label"
            :reduce="opt => opt.value"
            :clearable="clearable"
            @input="saveData"
        ></v-select>
        <ion-icon :class="{success: this.success}" name="checkmark-circle-outline" size="small"></ion-icon>
    </label>
</template>

<script>
export default {
    props: {
        route: String,
        label: {
            type: String,
            default: 'label',
        },
        clearable: {
            type: Boolean,
            default: true,
        },
        value: Object,
        placeholder: String,
        options: Array,
        action: {
            type: String,
            default: 'put',
        }
    },
    data() {
        return {
            success: false,
            selected: this.value,
        }
    },
    methods: {
        async saveData() {
            this.success = false;
            try {
                await axios[this.action](this.route, {value: this.selected});
                this.success = true;
                this.$emit('changed', this.selected);
                setTimeout(() => this.success = false, 2000);
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@import "../../../sass/_variables.scss";
ion-icon {
    transition: opacity 300ms ease-in-out;
    opacity: 0;
    color: $green;
    position: relative;
    top: -10px;
    left: -60px;
    visibility: visible;

    .no-clear & {
        left: -40px;
    }

    &.success {
        opacity: 1;
    }
}
.input-group .v-select .vs__dropdown-toggle {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}
</style>