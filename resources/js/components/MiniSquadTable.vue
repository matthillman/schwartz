<template>
    <table class="squad-table micro" :class="[styleClass, { fixed: !flexWidth }]" @dragstart.self="$emit('dragstart', $event)" @dragend="$emit('dragend', $event)">
        <thead v-if="!noHeader">
            <tr>
                <th :colspan="Math.min(squad.additional_members.length + 1, maxUnits)">
                    <div class="row justify-content-center"><span>{{ squad.display }}</span></div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="squad-row">
                <td class="top" v-for="char_id in [squad.leader_id, ...squad.additional_members.slice(0, Math.max(0, maxUnits - 2))]" :key="char_id">
                    <div class="column char-image-column">
                        <div class="char-image-square small" :class="[units[char_id].alignment]">
                            <img draggable="false" :src="`/images/units/${char_id}.png`">
                        </div>
                    </div>
                </td>
                <td v-if="squad.additional_members.length >= maxUnits - 2">
                    <div v-if="squad.additional_members.length == maxUnits - 1 && squad.additional_members.length" class="column char-image-column">
                        <div class="char-image-square small" :class="[units[ squad.additional_members[maxUnits - 2] ].alignment]">
                            <img :src="`/images/units/${squad.additional_members[maxUnits - 2]}.png`">
                        </div>
                    </div>
                    <div v-else-if="squad.additional_members.length > (maxUnits - 1)" class="column justify-content-center align-items-center extra-units">
                        <tooltip>
                            +{{ squad.additional_members.length - (maxUnits - 2) }}
                            <template #tooltip>
                                <table class="squad-table tooltip-table micro">
                                    <tbody>
                                        <tr class="squad-row tooltip-row">
                                            <td v-for="char_id in squad.additional_members.slice(maxUnits - 2)" :key="char_id">
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
                        </tooltip>
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
        noHeader: Boolean,
        maxUnits: {
            type: Number,
            default: 10,
        },
        flexWidth: Boolean,
        styleClass: String|Object,
    }
}
</script>

<style lang="scss" scoped>
.squad-table.fixed {
    table-layout: fixed;
}

th div {
    padding: 2px 4px;
}

.tooltip-row td {
    width: 43px;
    height: 40px;
}
</style>