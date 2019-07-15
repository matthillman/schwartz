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
                    {label: 'Legendaries', value: 'legendary'},
                    {label: 'Darth Malak', value: 'malak'},
                    {label: 'Padmé Amidala', value: 'padme'},
                    {label: 'Geo TB', value: 'geo'},
                    {label: 'Hoth TB', value: 'tb'},
                    {label: 'TW Defense', value: 'tw'},
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

