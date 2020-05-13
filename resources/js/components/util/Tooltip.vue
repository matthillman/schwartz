<template>
    <div>
        <div ref="trigger" class="pop-trigger"
            @mouseenter="doShow()"
            @focus="doShow()"
            @mouseleave="doHide()"
            @blur="doHide()"
        ><slot>Title</slot></div>
        <div ref="tooltip" :show="show" class="pop-tooltip">
            <slot name="tooltip"></slot>
            <div class="arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';
export default {
    props: {
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            show: false,
            instance: null,
        }
    },
    mounted() {
        this.layout();
    },
    methods: {
        layout() {
            this.instance = createPopper(this.$refs.trigger, this.$refs.tooltip, {
                modifiers: [
                    { name: 'offset', options: { offset: [0, 8] } },
                ],
            });
        },
        destroy() {
            if (this.instance) {
                this.instance.destroy();
                this.instance = null;
            }
        },

        doShow() {
            if (this.disabled) { return; }
            this.show = true;
            this.layout();
        },
        doHide() {
            this.show = false;
            this.destroy();
        }
    }
}
</script>

<style lang="scss" scoped>
.pop-trigger {
    cursor: pointer;
}
.pop-tooltip {
    background: #333;
    color: white;
    font-weight: bold;
    padding: 4px 8px;
    font-size: 13px;
    border-radius: 4px;
    display: none;
    z-index: 2000;

    &[show] {
        display: block;
    }

    .arrow {
        &, &::before {
            position: absolute;
            width: 8px;
            height: 8px;
            z-index: -1;
        }

        &::before {
            content: '';
            transform: rotate(45deg);
            background: #333;
        }
    }

    &[data-popper-placement^='top'] > .arrow {
        bottom: -4px;
    }

    &[data-popper-placement^='bottom'] > .arrow {
        top: -4px;
    }

    &[data-popper-placement^='left'] > .arrow {
        right: -4px;
    }

    &[data-popper-placement^='right'] > .arrow {
        left: -4px;
    }
}


</style>