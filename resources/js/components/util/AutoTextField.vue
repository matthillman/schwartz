<template>
    <label class="row no-margin align-items-end input-gropu form-group">
        <div class="column">
            <span class="small-note">{{ label }}</span>
            <input type="text" class="form-control" size="30" v-model="textValue">
        </div>
        <button class="btn btn-primary" @click="saveData">Save</button>
        <ion-icon :class="{success: this.success}" name="checkmark-circle-outline" size="small"></ion-icon>
    </label>
</template>

<script>
export default {
    props: {
        route: String,
        label: String,
        value: String,
        action: {
            type: String,
            default: 'put',
        }
    },
    data() {
        return {
            success: false,
            textValue: null,
        }
    },
    mounted() {
        this.textValue = this.value;
    },
    methods: {
        async saveData() {
            this.success = false;
            try {
                await axios[this.action](this.route, {value: this.textValue});
                this.success = true;
                this.$emit('changed', this.textValue);
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
    left: -77px;
    visibility: visible;

    &.success {
        opacity: 1;
    }
}

input {
    margin-right: 4px;
}
</style>