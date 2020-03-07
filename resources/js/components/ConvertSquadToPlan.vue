<template>
    <div>
        <button class="btn btn-icon with-text btn-primary trigger" @click="showCreate = true">
            <ion-icon name="skull" size="small"></ion-icon>
            <span>Create TW Plan</span>
        </button>

        <modal v-if="showCreate" @close="showCreate = false">
            <h3 slot="header">Create TW Plan from this Squad Group</h3>
            <div slot="body">
                <div class="row no-margin input-group">
                    <input class="form-control" type="text" placeholder="Plan Name" v-model="name">
                    <button class="btn btn-primary" @click="makePlan">Create</button>
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
        group: Object,
    },
    data() {
        return {
            showCreate: false,
            name: null,
            errors: [],
        }
    },
    methods: {
        async makePlan() {
            this.errors = [];
            try {
                const response = await axios.post(`/twp/squad/${this.group.id}`, { name: this.name });
                this.$root.go(response.data.route);
            } catch (e) {
                if (e.response.data.errors) {
                    this.errors = Array.isArray(e.response.data.errors) ? e.response.data.errors : [e.response.data.errors];
                } else {
                    this.errors = ["There was an error adding the squad group"];
                }
            }
        }
    }
}
</script>

<style lang="scss" scoped>
button.trigger {
    width: 100%;
    margin-top: 4px;
}
</style>