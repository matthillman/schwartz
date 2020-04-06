<template>
<div>
    <tab-list
        :tabs="tabs"
        :selected="selected"
        @changed="tabClicked"
    ></tab-list>

    <modal v-if="showCreateModal" @close="showCreateModal = false" wider>
        <h3 slot="header">Create a new Squad Group</h3>
        <div slot="body">
            <div class="row add-row input-group">
                <input class="form-control" type="text" placeholder="Group Name" v-model="name">
                <v-select :options="guilds" placeholder="Guild" v-model="squadGuild"></v-select>
                <button class="btn btn-primary striped" @click="makeGroup"><span>Add Group</span></button>
            </div>
        </div>
        <div slot="footer">
            <div class="error" v-for="(error, id) of errors" :key="id">
                {{ error }}
            </div>
        </div>
    </modal>
    <modal v-if="showEditModal" @close="showEditModal = false">
        <h3 slot="header">Edit Squad Groups</h3>
        <div slot="body">
            <div class="column justify-content-center">
                <div v-for="group in tabs.filter(g => g.index > 1)" :key="group.index" class="row no-margin align-items-center justify-content-center">
                    <auto-text-field
                        :route="`/squads/group/${group.index}`"
                        :label="`Name`"
                        :value="group.title"
                        @changed="val => update(group, val)"
                    >
                        <button class="btn btn-danger btn-icon striped" @click.prevent="deleteGroup = group"><ion-icon name="trash" size="small"></ion-icon></button>
                    </auto-text-field>
                </div>
            </div>
        </div>
        <div slot="footer">
            <div class="error" v-for="(error, id) of errors" :key="id">
                {{ error }}
            </div>
        </div>
    </modal>
    <modal v-if="deleteGroup" @close="deleteGroup = false">
        <h3 slot="header">Delete Squad Groups</h3>
        <div slot="body">
            <div>Are you sure you want to delete the group “{{ deleteGroup.title }}”??? You can't undo this.</div>
        </div>
        <div slot="footer">
            <div slot="footer">
                <div class="error" v-for="(error, id) of errors" :key="id">
                    {{ error }}
                </div>
                <button class="btn btn-danger striped" @click="removeGroup(deleteGroup)"><span>Delete</span></button>
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
            showEditModal: false,
            deleteGroup: null,
            squadGuild: null,
            name: null,
            errors: [],
            tabs: [],
        }
    },
    mounted() {
        this.tabs = this.groups;
    },
    methods: {
        tabClicked(tab) {
            if (tab.index == -1) {
                this.showCreateModal = true;
            } else if (tab.index == -2) {
                this.showEditModal = true;
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
                    this.errors = Array.isArray(e.response.data.errors) ? e.response.data.errors : [e.response.data.errors];
                } else {
                    this.errors = ["There was an error adding the squad group"];
                }
            }
        },
        async removeGroup(group) {
            this.errors = [];
            try {
                const response = await axios.delete(`/squads/group/${group.index}`);
                this.$root.go(`/squads`);
            } catch (e) {
                if (e.response.data.errors) {
                    this.errors = e.response.data.errors;
                } else {
                    this.errors = ["There was an error deleting the squad group"];
                }
            }
        },
        update(group, val) {
            let toUpdate = this.tabs.find(g => g.index == group.index);
            toUpdate.title = val;
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
