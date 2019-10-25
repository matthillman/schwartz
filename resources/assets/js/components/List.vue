<template>
	<div class="list-table">
	    <table class="sortable">
	        <thead>
	            <tr>
	                <th v-for="(column, index) in columns"
                        :key="index"
	                    @click="sortBy(column.prop)"
	                    class="clickable"
	                    :class="{sorted: sorted === column.prop, reverse: sorted === column.prop && reversed}"
	                ><span>{{ column.label }}</span></th>
	            </tr>
	        </thead>
	        <tbody>
	            <tr v-for="(item, index) in items" :key="index">
	                <td v-for="column in columns" :key="column.prop">
                        <a v-if="column.href" :href="resolve(item, column.href)">{{ resolve(item, column.prop, column.transform) }}</a>
                        <span v-if="!column.href">{{ resolve(item, column.prop, column.transform) }}</span>
                    </td>
	            </tr>
	        </tbody>
	    </table>
    </div>
</template>

<script>
    export default {
        props: {
            items: Array,
            columns: Array,
        },
        data: function () {
            return {
				sorted: this.columns[1].prop,
				reversed: true
            }
        },
        methods: {
            resolve: function(item, prop, transform) {
                let v = item;
                let props = prop.split('.');
                for (let i = 0; i < props.length; i++) {
                    if (!v) return undefined;
                    v = v[props[i]];
                }
                return transform ? transform(v) : v;
            },
            sortBy: function(prop) {
            	this.reversed = this.sorted === prop && !this.reversed;
            	this.sorted = prop;
                this.$emit('sort', prop, this.reversed);
            }
        }
    }
</script>

<style lang="scss" scoped>
	.total {
		font-weight: bolder;
		> span {
			font-weight: normal;
		}
	}
    .clickable {
        cursor: pointer;

        &:hover {
            text-decoration: underline;
        }
    }
</style>