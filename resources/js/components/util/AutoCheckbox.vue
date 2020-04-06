<template>
    <div class="row align-items-center no-margin wrapper">
        <button
            v-if="button"
            class="btn btn-icon with-text btn-primary striped"
            @click="isChecked = !isChecked"
        >
            <ion-icon :name="iconName" size="small"></ion-icon>
            <span>{{ label }}</span>
        </button>
        <label v-else>
            <input type="checkbox" v-model="isChecked">
            <span>{{ label }}</span>
        </label>
        <ion-icon class="status" :class="{success: this.success}" name="checkmark-circle-outline" size="small"></ion-icon>
    </div>
</template>

<script>
export default {
    props: {
        route: String,
        label: String,
        checked: Boolean,
        button: Boolean,
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
    computed: {
        iconName() {
            return this.isChecked ? 'checkbox' : 'square';
        }
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
@import "../../../sass/_variables.scss";
.wrapper {
    position: relative;
}
ion-icon.status {
    transition: opacity 300ms ease-in-out;
    opacity: 0;
    color: $green;
    position: absolute;
    right: -16px;
    visibility: visible;

    &.success {
        opacity: 1;
    }
}

input {
    margin-right: 4px;
}
</style>