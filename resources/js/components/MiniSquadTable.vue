<template>
    <table class="squad-table micro">
        <thead>
            <tr>
                <th :colspan="squad.additional_members.length + 1">
                    <div class="row justify-content-center"><span>{{ squad.display }}</span></div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="squad-row">
                <td class="top" v-for="char_id in [squad.leader_id, ...squad.additional_members]" :key="char_id">
                    <div class="column char-image-column">
                        <div class="char-image-square small" :class="[units[char_id].alignment]">
                            <img :src="`/images/units/${char_id}.png`">
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    props: {
        squad: Object,
        units: Object,
    }
}
</script>