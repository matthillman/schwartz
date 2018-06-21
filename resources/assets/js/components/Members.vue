<template>
	<div class="list">
        <div class="control-wrapper">
            <div class="segmented-control">
                <span v-for="(guild, index) in guildList" :key="guild.id"
                    @click="select(index)"
                    :class="{selected: selected === index}"
                >{{ guild.name | acronymize }}</span>
            </div>
        </div>
	    <list
	    	:columns="columns"
	    	:items="items"
	    	v-on:sort="sort"
	    ></list>
    </div>
</template>

<script>
    const numberWithCommas = (x) => {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    const acronymize = (str) => str.split(/(\s|[A-Z]\w+)/).map((w) => w ? w[0].toUpperCase().trim() : '').join('');

    export default {
        mounted() {
            this.guildList = JSON.parse(this.guilds);
            this.refresh();
        },
        filters: {
            acronymize: acronymize
        },
        data: function () {
            return {
                route: 'gp',
                items: [],
                columns: [
		            { prop: 'player', label: 'Member', href: 'url' },
		            { prop: 'gp', label: 'Galactic Power', transform: numberWithCommas },
		            { prop: 'character_gp', label: 'Character GP', transform: numberWithCommas },
		            { prop: 'ship_gp', label: 'Ship GP', transform: numberWithCommas },
		            { prop: 'guild_name', label: 'Guild', transform: acronymize  },
                ],
                selected: 0,
                guildList: [],
            }
        },
        props: {
            guilds: {
                type: String
            }
        },
        methods: {
            select: function(index) {
                this.selected = index;
                this.refresh();
            },
            refresh: function() {
                axios.get(`/${this.route}/${this.guildList[this.selected].id}`)
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
</style>