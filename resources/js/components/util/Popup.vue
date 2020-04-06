<template>
    <div>
        <div ref="trigger" class="pop-trigger"
            @click.prevent.stop="show ? doHide() : doShow()"
        ><slot>Title</slot></div>
        <div ref="menu" :show="show" class="pop-menu">
            <slot name="menu"></slot>
            <div class="arrow" data-popper-arrow></div>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';
export default {
    data() {
        return {
            show: false,
            instance: null,
            appendedToBody: false,
        }
    },
    mounted() {
        this.layout();
    },
    methods: {
        layout() {
            this.instance = createPopper(this.$refs.trigger, this.$refs.menu, {
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
            if (!this.appendedToBody) {
                this.appendedToBody = true;
                document.body.appendChild(this.$refs.menu);
            }
            document.addEventListener('click', this.documentClick, false);
            this.show = true;
            this.layout();
        },
        doHide() {
            if (this.appendedToBody) {
                this.appendedToBody = false;
                document.body.removeChild(this.$refs.menu);
            }
            document.removeEventListener('click', this.documentClick, false);
            this.show = false;
            this.destroy();
        },
        documentClick(e) {
            if (!this.show) {
                return;
            }

            if (this.$el.contains(e.target) || this.$refs.menu.contains(e.target)) {
                return;
            }

            this.doHide();
        }
    }
}
</script>

<style lang="scss" scoped>
.pop-trigger {
    cursor: pointer;
}
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