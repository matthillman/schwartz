<template>
    <tooltip>
        <ion-icon name="help-circle" :size="size"></ion-icon>
        <template #tooltip>
            <slot></slot>
        </template>
    </tooltip>
</template>

<script>
export default {
    props: {
        size: {
            type: String,
            default: "small",
        }
    }
}
</script>