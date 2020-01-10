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
            <div><span>6•:</span> {{ items | sumProp('six_dot') | numberWithCommas }}</div>
            <div><span>25+ Speed:</span> {{ items | sumProp('speed_25') | numberWithCommas }}</div>
            <div><span>20+ Speed:</span> {{ items | sumProp('speed_20') | numberWithCommas }}</div>
            <div><span>15+ Speed:</span> {{ items | sumProp('speed_15') | numberWithCommas }}</div>
            <div><span>10+ Speed:</span> {{ items | sumProp('speed_10') | numberWithCommas }}</div>
            <div><span>100+ Offense:</span> {{ items | sumProp('offense_100') | numberWithCommas }}</div>
        </div>
	    <list
	    	:columns="columns"
	    	:items="items"
	    	v-on:sort="sort"
	    ></list>

        <modal v-if="syncing" @close="syncing = null" no-close>
            <h3 slot="header">Querying Mods</h3>
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
            if (this.mods && this.mods.length) {
                this.items = this.mods;
                this.sort(this.columns[1].prop, true);
            } else {
                this.refresh();
            }
        },
        filters: {
            acronymize: acronymize,
            numberWithCommas: numberWithCommas,
            sumProp: function(value, prop) {
                return value.reduce((sum, cur) => sum + cur[prop], 0)
            }
        },
        data: function () {
            return {
                route: 'guild_mods',
                items: [],
                columns: [
		            { prop: 'player', label: 'Member', href: 'url' },
		            { prop: 'six_dot', label: '6•', transform: numberWithCommas },
		            { prop: 'speed_25', label: 'Speed 25+', transform: numberWithCommas },
		            { prop: 'speed_20', label: 'Speed 20+', transform: numberWithCommas },
		            { prop: 'speed_15', label: 'Speed 15+', transform: numberWithCommas },
		            { prop: 'speed_10', label: 'Speed 10+', transform: numberWithCommas },
		            { prop: 'offense_100', label: 'Offense 100+', transform: numberWithCommas },
                ],
                selected: 0,
                syncing: false,
            }
        },
        props: {
            guilds: Array,
            mods: Array,
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