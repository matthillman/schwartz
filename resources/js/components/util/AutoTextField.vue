<template>
    <label class="row no-margin align-items-end input-group form-group">
        <div class="column">
            <span class="small-note">{{ label }}</span>
            <input type="text" class="form-control" size="30" v-model="textValue">
        </div>
        <button class="btn btn-primary striped" @click="saveData"><span>Save</span></button>
        <slot></slot>
        <ion-icon class="indicator" :class="{success: this.success}" name="checkmark-circle-outline" size="small"></ion-icon>
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
ion-icon.indicator {
    transition: opacity 300ms ease-in-out;
    opacity: 0;
    color: $green;
    position: absolute;
    top: 25px;
    left: 225px;
    visibility: visible;

    &.success {
        opacity: 1;
    }
}

input {
    margin-right: 4px;
}

.input-group > * {
    margin-right: 4px;

    &:last-child {
        margin-right: 0;
    }

    &:not(div) {
        margin-bottom: 2px;
    }
}
</style>