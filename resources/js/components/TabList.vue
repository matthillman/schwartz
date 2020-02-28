<template>
    <div class="squad-switcher row justify-content-start align-items-center">
        <a v-if="allRoute" class="btn" :class="{selected: selected > tabs.length}" :href="allRoute">All</a>
        <a
            class="btn"
            v-for="tab in tabs"
            :key="tab.title"
            :ref="tab.title"
            :class="{selected: selected == tab.index}"
            @click.prevent="$emit('changed', tab)"
        >
            <ion-icon v-if="tab.icon" :name="tab.icon" size="medium"></ion-icon>
            <span v-if="!tab.iconOnly">{{ tab.title }}</span>
        </a>

        <popover v-if="hiddenTabs.length" class="teams right" :name="`more-tabs`">
            <div slot="face">
                <button class="btn btn-primary inverted btn-icon">
                    <ion-icon name="ellipsis-horizontal-circle-outline" size="medium"></ion-icon>
                </button>
            </div>
            <div slot="content">
                <ul>
                    <li v-for="tab in hiddenTabs" :key="tab.title"><a :class="{selected: selected == tab.index}" @click.prevent="$emit('changed', tab)">{{ tab.title }}</a></li>
                </ul>
            </div>
        </popover>
    </div>
</template>

<script>
export default {
    props: {
        tabs: Array,
        allRoute: String,
        selected: Number,
    },
    data() {
        return {
            hiddenTabs: [],
        };
    },
    mounted() {
        window.addEventListener('resize', this.makeMoreTabs);
    },
    updated() {
        this.$nextTick(function() {
            this.makeMoreTabs();
        });
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.makeMoreTabs);
    },
    methods: {
        makeMoreTabs() {
            const hiddenTabs = [];
            const tabElements = Object.keys(this.$refs).map(label => {
                const el = this.$refs[label][0];
                return {
                    tab: this.tabs.find(t => t.title == label),
                    el,
                    visible: this.isInViewPort(el)
                };
            })
            for (const child of tabElements) {
                if (!child.visible) {
                    hiddenTabs.push(child.tab);
                    child.el.hidden = true;
                } else {
                    child.el.hidden = false;
                }
            }
            this.hiddenTabs = hiddenTabs;
        },

        isInViewPort(elem) {
            const bounds = elem.getBoundingClientRect();
            const parentBounds = elem.parentElement.getBoundingClientRect();
            return bounds.top >= parentBounds.top && bounds.right <= parentBounds.right && bounds.bottom <= parentBounds.bottom && bounds.left >= parentBounds.left;
        }
    }
}
</script>

<style lang="scss" scoped>
.squad-switcher {
    flex-wrap: nowrap;

    a.btn {
        white-space: nowrap
    }
}

.popover.teams.right {
    background: none;
    &.open {
        background: rgba($color: #000000, $alpha: 0.1);
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
}
</style>
<style lang="scss" scoped>
.squad-switcher {
    &, & > a {
        height: 38px;
    }
}
</style>
<style lang="scss">
@import "../../sass/_variables.scss";
.popover.teams.right {
    .popover__container {
        right: 0;
        left: unset;
    }
    &.open {
        .popover__container {
            &, & ul {
                border-top-left-radius: 8px;
                border-top-right-radius: 0;

                a.selected {
                    background: $primary;
                    color: $white;
                }
            }
        }
    }
}
</style>