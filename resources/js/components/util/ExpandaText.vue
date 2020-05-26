<template>
    <form method="POST" :action="action">
        <slot></slot>
        <input
            type="text"
            class="form-control"
            :name="name"
            v-model="txt"
            :ref="'input'"
            :class="{focused: txt.length}"
        >
        <button type="submit" @click="focusOrSubmit($event)" class="btn btn-primary btn-icon striped">
            <ion-icon :name="txt.length ? 'checkmark-done' : icon" size="medium"></ion-icon>
        </button>
    </form>
</template>

<script>
export default {
    props: {
        action: String,
        name: String,
        icon: String,
    },
    data() {
        return {
            txt: '',
        }
    },
    methods: {
        focusOrSubmit(e) {
            if (!this.txt.length) {
                e.preventDefault();
                this.$refs.input.focus();
            }
        }
    }
}
</script>

<style lang="scss" scoped>
form {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
input {
    width: 0;
    max-width: 200px;
    text-align: center;
    transition-property: width, border-color;
    transition-duration: 0.25s;
    transition-timing-function: ease-in;
    border-color: transparent;
    background: transparent;
    margin-right: 4px;

    &:focus, &.focused {
        border-color: initial;
        background-color: initial;
        width: 10em;
    }
}
</style>