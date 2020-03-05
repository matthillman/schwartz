<template>
    <div>
        <button class="btn btn-icon btn-primary">Create TW Plan</button>

        <modal v-if="showCreate" @close="showCreate = false">
            <h3 slot="header">Create TW Plan from this Squad Group</h3>
            <div slot="body">
                <div class="row add-row input-group">
                    <input class="form-control" type="text" placeholder="Plan Name" v-model="name">
                    <v-select :options="guilds" placeholder="Guild" v-model="squadGuild"></v-select>
                    <button class="btn btn-primary" @click="makeGroup">Add Group</button>
                </div>
            </div>
            <div slot="footer">
                <div class="error" v-for="(error, id) of errors" :key="id">
                    {{ error }}
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
export default {
    props: {
        guilds: Array,
        group: Object,
    }
}
</script>