<template>
    <div>
        <div ref="menu" :show="!!this.anchor" class="pop-menu">
            <slot></slot>
            <div class="arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';
export default {
    props: {
        anchor: Element,
    },
    data() {
        return {
            instance: null,
            appendedToBody: false,
        }
    },
    watch: {
        anchor() {
            if (this.anchor) {
                this.doShow();
            } else {
                this.doHide();
            }
        },
    },
    methods: {
        layout() {
            if (this.anchor) {
                this.instance = createPopper(this.anchor, this.$refs.menu, {
                    placement: 'left',
                    modifiers: [
                        { name: 'offset', options: { offset: [0, 8] } },
                    ],
                });
            }
        },
        destroy() {
            if (this.instance) {
                this.instance.destroy();
                this.instance = null;
            }
        },

        doShow() {
            if (!this.appendedToBody) {
                this.appendedToBody = true;
                document.body.appendChild(this.$refs.menu);
            }
            this.layout();
        },
        doHide() {
            if (this.appendedToBody) {
                this.appendedToBody = false;
                document.body.removeChild(this.$refs.menu);
            }
            this.destroy();
        },
    }
}
</script>

<style lang="scss" scoped>
.pop-menu {
    color: #333;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 2px black;
    font-weight: bold;
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
            background: white;
            border: 1px solid #adb5bd;
            border-right-color: transparent;
            border-bottom-color: transparent;
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