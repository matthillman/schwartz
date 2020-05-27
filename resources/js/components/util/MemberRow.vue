<template>
    <div class="row justify-content-between w-full">
         <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" v-model="$root.memberCompareArray" :value="member.ally_code">
        </div>
        <div class="grow">
            <h4>{{ member.player }}</h4>
            <div class="small-note">{{ member.ally_code.replace(/^(\d{3})(\d{3})(\d{3})$/, "$1–$2–$3") }}</div>
            <div class="small-note">{{ member.gp.toLocaleString() }} GP</div>
        </div>

        <div class="column align-items-end">

            <h4>{{ member.guild.name || 'Guildless' }}</h4>

            <div class="row no-margin align-items-center justify-content-center item-margin">
                <status :status="$root.modJobStatusByAllyCode[member.ally_code]"></status>
                <tooltip>
                    <button type="button" @click="$root.go(`/member/${member.ally_code}`)" class="btn btn-primary btn-icon striped" title="Profile"><ion-icon name="person" size="medium"></ion-icon></button>
                    <template #tooltip>Profile</template>
                </tooltip>
                <tooltip>
                    <button type="button" @click="$root.go(`/member/${member.ally_code}/characters`)"  class="btn btn-primary btn-icon striped" title="Characters"><ion-icon name="people-circle-outline" size="medium"></ion-icon></button>
                    <template #tooltip>Characters</template>
                </tooltip>
                <tooltip>
                    <button type="button" @click="$root.go(`/member/${member.ally_code}/ships`)"  class="btn btn-primary btn-icon striped" title="Ships"><ion-icon name="planet" size="medium"></ion-icon></button>
                    <template #tooltip>Ships</template>
                </tooltip>
                <a :href="member.url" target="_gg" class="gg-link striped round">
                    <bb8></bb8>
                </a>
                <button @click="doRefresh" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        member: Object,
    },
    data() {
        return {
            memberCompareArray: [],
            modJobStatusByAllyCode: []
        }
    },
    methods: {
        async doRefresh() {
            await axios.put(`/member/refresh`, {
                id: this.member.id,
            });

            this.$root.loadModJobStatus();

            this.$toasted.global.striped(`Data update for ${this.member.player} has been queued…`);
        }
    }
}
</script>