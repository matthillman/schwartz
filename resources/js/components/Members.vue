<template>
	<div class="list">
        <div class="control-wrapper" v-if="guilds.length > 1">
            <div class="segmented-control">
                <span v-for="(guild, index) in guilds" :key="guild.id"
                    @click="select(index)"
                    :class="{selected: selected === index}"
                >{{ guild.name | acronymize }}</span>
            </div>
        </div>
        <div class="flex-center align-items-center" v-if="guilds.length === 1">
            <h1>{{ guilds[0].name }}</h1>
        </div>
        <div class="flex-center spacing">
            <div><span>Total:</span> {{ items | sumProp('gp') | numberWithCommas }}</div>
            <div><span>Char GP:</span> {{ items | sumProp('character_gp') | numberWithCommas }}</div>
            <div><span>Ship GP:</span> {{ items | sumProp('ship_gp') | numberWithCommas }}</div>
        </div>
        <div class="row no-margin justify-content-around align-items-center dark">
            <div class="row no-margin justify-content-center align-items-center">
                <div class="portrait relic-only centered">
                    <div class="relic"><span class="value">7</span></div>
                </div>
                <div> {{ items | sumProp('relic_7') | numberWithCommas }}</div>
            </div>
            <div class="row no-margin justify-content-center align-items-center dark">
                <div class="portrait relic-only centered">
                    <div class="relic"><span class="value">6</span></div>
                </div>
                <div> {{ items | sumProp('relic_6') | numberWithCommas }}</div>
            </div>
            <div class="row no-margin justify-content-center align-items-center dark">
                <div class="portrait relic-only centered">
                    <div class="relic"><span class="value">5</span></div>
                </div>
                <div> {{ items | sumProp('relic_5') | numberWithCommas }}</div>
            </div>

            <div class="row no-margin justify-content-center align-items-center dark">
                <div class="portrait mini centered">
                    <div class="gear g13" style="--gear-image:url('/images/units/gear/gear-icon-g13.png');"></div>
                    <span class="value">13</span>
                </div>
                <div> {{ items | sumProp('gear_13') | numberWithCommas }}</div>
            </div>
            <div class="row no-margin justify-content-center align-items-center dark">
                <div class="portrait mini centered">
                    <div class="gear g12" style="--gear-image:url('/images/units/gear/gear-icon-g12.png');"></div>
                    <span class="value">12</span>
                </div>
                <div> {{ items | sumProp('gear_12') | numberWithCommas }}</div>
            </div>

        </div>
	    <list
	    	:columns="columns"
	    	:items="items"
	    	v-on:sort="sort"
	    ></list>

        <modal v-if="syncing" @close="syncing = null" no-close>
            <h3 slot="header">Querying Members</h3>
            <div slot="body">
                <div class="flex-center">
                    Waiting and parsing and such
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    const numberWithCommas = (x) => {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    const acronymize = (str) => str.split(/(\s|[A-Z]\w+)/).map((w) => w ? w[0].toUpperCase().trim() : '').join('');

    export default {
        mounted() {
            if (this.members && this.members.length) {
                this.items = this.members;
                this.sort(this.columns[1].prop, true);
            } else {
                this.refresh();
            }
        },
        filters: {
            acronymize: acronymize,
            numberWithCommas: numberWithCommas,
            sumProp: function(value, prop) {
                return value.reduce((sum, cur) =>sum + cur[prop], 0)
            }
        },
        data: function () {
            return {
                route: 'gp',
                items: [],
                columns: [
		            { prop: 'player', label: 'Member', href: 'profile_url' },
		            { prop: 'gp', label: 'Galactic Power', transform: numberWithCommas },
		            { prop: 'character_gp', label: 'Character GP', transform: numberWithCommas },
		            { prop: 'ship_gp', label: 'Ship GP', transform: numberWithCommas },
		            { prop: 'gear_13', label: 'Gear 13', transform: numberWithCommas },
		            { prop: 'gear_12', label: 'Gear 12', transform: numberWithCommas },
		            { prop: 'relic_7', label: 'R7', transform: numberWithCommas  },
		            { prop: 'relic_6', label: 'R6', transform: numberWithCommas  },
		            { prop: 'relic_5', label: 'R5', transform: numberWithCommas  },
                ],
                selected: 0,
                syncing: false,
            }
        },
        props: {
            guilds: Array,
            members: Array,
        },
        methods: {
            select: function(index) {
                this.selected = index;
                this.refresh();
            },
            refresh: function() {
                this.syncing = true;
                axios.get(`/${this.route}/${this.guilds[this.selected].id}`)
                    .then(res => {
                        this.items = res.data;
                        this.sort(this.columns[1].prop, true);
                    });
            },
            resolve: function(item, prop) {
                let v = item;
                let props = prop.split('.');
                for (let i = 0; i < props.length; i++) {
                    if (!v) return undefined;
                    v = v[props[i]];
                }
                return v;
            },
            sort: function(prop, reversed) {
                this.items = this.items.sort((a, b) => {
					let aProp = this.resolve(a, prop);
					let bProp = this.resolve(b, prop);
                    if (typeof aProp === typeof "") {
                        return aProp.localeCompare(bProp);
                    }
                    return aProp - bProp;
                });

				if (reversed) {
					this.items = this.items.reverse();
				}
                this.syncing = false;
            },
        }
    }
</script>

<style lang="scss" scoped>
.subhead {
	font-size: 16px;
	font-weight: bold;
	margin: 0;
}
.filters {
	display: flex;
	margin-bottom: 24px;

	> * {
		margin-right: 12px;
	}
}
.list-table table td:first-of-type {
    max-width: 160px;
}
</style>