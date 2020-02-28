<template>
    <label class="row align-items-baseline">
        <input type="checkbox" v-model="isChecked">
        <span>{{ label }}</span>
        <ion-icon :class="{success: this.success}" name="checkmark-circle-outline" size="small"></ion-icon>
    </label>
</template>

<script>
export default {
    props: {
        route: String,
        label: String,
        checked: Boolean,
    },
    data() {
        return {
            success: false,
            isChecked: null,
        }
    },
    mounted() {
        this.isChecked = this.checked;
    },
    watch: {
        isChecked: 'saveData'
    },
    methods: {
        async saveData(newVal, oldVal) {
            if (oldVal === null || newVal === oldVal) { return; }
            this.success = false;
            try {
                await axios.put(this.route, {value: this.isChecked});
                this.success = true;
                setTimeout(() => this.success = false, 2000);
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@import "../../sass/_variables.scss";
ion-icon {
    transition: opacity 300ms ease-in-out;
    opacity: 0;
    color: $green;
    position: relative;
    top: 4px;
    margin-left: 4px;
    visibility: visible;

    &.success {
        opacity: 1;
    }
}

input {
    margin-right: 4px;
}
</style>