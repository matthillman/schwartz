<template>
    <div class="select-wrapper">
        <v-select :options="teams" @input="goToTeam"></v-select>
    </div>
</template>

<script>
    export default {
        props: ['id'],
        data: function() {
            return {
                teams: [
                    {label: 'STR', value: 'str'},
                    {label: 'RJT', value: 'rjt'},
                    {label: 'Bounty Hunters', value: 'bh'},
                    {label: 'Newie', value: 'newie'},
                    {label: 'OG Falcon', value: 'falcon'},
                    {label: 'Revan', value: 'kotor'},
                    {label: 'C3PO', value: 'ewoks'},
                    {label: 'TW Defense', value: 'tw'},
                    {label: 'TB', value: 'tb'},
                ],
            }
        },
        methods: {
            goToTeam: function(team) {
                window.location.replace(`${window.location.origin}/guild/${this.id}/${team.value}`);
            }
        }
    }
</script>

<style lang="scss" scoped>
.select-wrapper {
    width: 200px;
    max-width: 100%;
}
</style>

