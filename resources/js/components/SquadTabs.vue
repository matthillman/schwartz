<template>
<div>
    <tab-list
        :tabs="groups"
        :selected="selected"
        @changed="tabClicked"
    ></tab-list>

    <modal v-if="showCreateModal" @close="showCreateModal = false" wider>
        <h3 slot="header">Create a new Squad Group</h3>
        <div slot="body">
            <div class="row add-row input-group">
                <input class="form-control" type="text" placeholder="Group Name" v-model="name">
                <v-select :options="guilds" placeholder="Guild" v-model="squadGuild"></v-select>
                <button class="btn btn-primary" @click="makeGroup">Add Group</button>
            </div>
        </div>
        <div slot="footer">
            <div class="error" v-for="(error, id) of errors" :key="id">
                {{ error.join("\n") }}
            </div>
        </div>
    </modal>
</div>
</template>

<script>
export default {
    props: {
        groups: Array,
        guilds: Array,
        selected: Number,
    },
    data() {
        return {
            showCreateModal: false,
            squadGuild: null,
            name: null,
            errors: [],
        }
    },
    methods: {
        tabClicked(tab) {
            if (tab.index == -1) {
                this.showCreateModal = true;
            } else {
                this.$root.go(`/squads?group=${tab.index}`);
            }
        },
        async makeGroup() {
            this.errors = [];
            try {
                const response = await axios.post(`/squads/group`, { name: this.name, guild: this.squadGuild.value});
                this.$root.go(`/squads?group=${response.data.id}`);
            } catch (e) {
                if (e.response.data.errors) {
                    this.errors = e.response.data.errors;
                } else {
                    this.errors = ["There was an error adding the squad group"];
                }
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@import "../../sass/_variables.scss";
.error {
    color: $red;
}

.add-row {
    > :not(:last-child) {
        flex-basis: calc((100% - 126px) / 2);
    }

    > :last-child {
        flex: 0 0 110px;
    }
}
</style>
