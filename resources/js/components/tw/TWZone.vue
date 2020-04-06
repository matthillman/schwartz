<template>
    <div class="card-body">
        <div v-if="!dragMode">
            <div class="small-note">Notes:</div>
            <div class="notes-field" contenteditable @blur="updateNotes">{{ notes }}</div>
        </div>
        <div v-if="!dragMode" class="form-group row no-margin align-items-start">
            <v-select
                class="grow"
                :options="availableSquads()"
                placeholder="Select Squad"
                v-model="selectedSquad"
                :label="'display'"
                append-to-body
            >
                <template v-slot:option="squad">
                    <mini-squad-table :squad="squad" :units="units"></mini-squad-table>
                </template>
                <template v-slot:selected-option="squad">
                    <mini-squad-table :squad="squad" :units="units"></mini-squad-table>
                </template>
            </v-select>
            <button class="btn btn-primary striped" @click="addSquad"><span>Add to Zone</span></button>
        </div>

        <div v-for="(zoneMembers, squadID) in zoneData" :key="`${squadID}-${zoneMembers.length}`"
            class="drop-target"
            :class="{ over: dragTarget == squadID, targetable: dragMode, 'not-dropable': !dropOK }"
            @dragover.prevent="onDragOver(squadID, $event)"
            @dragenter="onDragEnter(squadID, $event)"
            @dragleave.self="onDragLeave"
            @drop.prevent.stop="onDrop(squadID, $event)"
            @dragend="onDragLeave"
        >
            <div class="squad-wrapper">
                <mini-squad-table :squad="squads[squadID]" :units="units" flex-width></mini-squad-table>
                <button class="btn btn-danger btn-icon inverted" @click="deleteSquad(squads[squadID])"><ion-icon name="remove-circle" size="small"></ion-icon></button>
            </div>

            <div v-show-slide="!dragMode" class="zone-member-wrapper">
                <table v-for="ally_code of zoneMembers" :key="ally_code" class="squad-table micro">
                    <tbody>
                        <tr class="squad-row player-info">
                            <td>
                                <div class="name-wrapper">
                                    <div class="column">
                                        <div>{{ nameForMember(ally_code) }}</div>
                                        <div class="small-note">Power: {{ [squads[squadID].leader_id, ...squads[squadID].additional_members].map(c => charForMember(ally_code, c)).reduce((p, c) => p + (c ? c.power : 0), 0).toLocaleString() }}</div>
                                    </div>

                                    <button class="btn btn-danger btn-icon inverted" @click="deleteMember(squads[squadID], memberFor(ally_code))"><ion-icon name="remove-circle" size="small"></ion-icon></button>
                                </div>
                            </td>
                            <td v-for="base_id in [squads[squadID].leader_id, ...squads[squadID].additional_members.slice(0, 4)]" :key="base_id">
                                <div v-if="charForMember(ally_code, base_id)">
                                    <character
                                        :character="charForMember(ally_code, base_id)"
                                        no-stats no-mods no-zetas
                                        :classes="'mini'"
                                    ></character>
                                </div>
                            </td>
                            <td v-if="squads[squadID].additional_members.length > 4">
                                <div class="column justify-content-center align-items-center extra-units">
                                    <tooltip>
                                        +{{ squads[squadID].additional_members.length - 4 }}
                                        <template #tooltip>
                                            <table class="squad-table micro">
                                                <tbody>
                                                    <tr class="squad-row tooltip-row">
                                                        <td v-for="char_id in squads[squadID].additional_members.slice(4)" :key="char_id">
                                                            <div v-if="charForMember(ally_code, char_id)">
                                                            <character
                                                                :character="charForMember(ally_code, char_id)"
                                                                no-stats no-mods no-zetas
                                                                :classes="'mini'"
                                                            ></character>
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
            </div>

            <div v-if="!dragMode" class="form-group row no-margin align-items-start add-member">
                <v-select
                    class="grow"
                    :options="availableMembers(squadID)"
                    placeholder="Add Member"
                    v-model="selectedMember[squadID]"
                    :label="'player'"
                    append-to-body
                    :calculate-position="withPopper"
                    :selectable="member => memberAvailable(member, squadID)"
                >
                </v-select>
                <button class="btn btn-primary striped" @click="addMember(squadID)"><span>Add</span></button>
                <button class="btn btn-secondary btn-icon striped" @click="$emit('add-multiple', zone, squadID)">
                    <tooltip>
                        <ion-icon name="duplicate" size="small"></ion-icon>
                        <template #tooltip>
                            Add multiple members
                        </template>
                    </tooltip>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';

export default {
    props: {
        zone: Number,
        zoneData: Object,
        notes: String,
        squads: Object,
        units: Object,
        members: Array,
        dragMode: Boolean,
    },
    data() {
        return {
            selectedSquad: null,
            selectedMember: {},
            dragTarget: null,
            dropOK: false,
        };
    },
    methods: {
        addSquad() {
            if (this.selectedSquad == null) { return; }
            this.$emit('add-squad', this.zone, this.selectedSquad.id);
            this.selectedSquad = null;
        },
        addMember(squadID) {
            if (this.selectedMember[squadID] == null) { return; }
            this.$emit('add-member', this.zone, squadID, this.selectedMember[squadID]);
            this.selectedMember[squadID] = null;
        },
        deleteSquad(squad) {
            this.$emit('remove-squad', this.zone, squad.id);
        },
        deleteMember(squad, member) {
            this.$emit('remove-member', this.zone, squad.id, member);
        },
        updateNotes(el) {
            this.$emit('update-notes', this.zone, el.target.innerText);
        },

        availableSquads() {
            const usedSquads = Object.keys(this.zoneData).map(id => parseInt(id));
            return Object.values(this.squads)
                .filter(s => !usedSquads.includes(s.id))
                .filter(s => (this.units[s.leader_id].combat_type == 2) === [5, 8].includes(this.zone))
            ;
        },
        availableMembers(squadID) {
            return this.members
                .filter(m => !this.zoneData[squadID].includes(m.ally_code));
        },
        memberAvailable(member, squadID) {
            if (!member || !member.ally_code) { return false; }
            return !this.zoneData[squadID].includes(member.ally_code)
            && (!member.usedSquads || !member.usedSquads.has(this.squads[squadID].leader_id));
        },
        memberFor(ally_code) {
            return this.members.find(m => m.ally_code == ally_code) || { player: `BOB (${ally_code})`, ally_code, characters: [] };
        },
        charForMember(ally_code, base_id) {
            const member = this.memberFor(ally_code);
            const char = member.characters.filter(c => c.unit_name == base_id);
            if (char.length) { return char[0]; }
            return null;
        },
        nameForMember(ally_code) {
            return this.memberFor(ally_code).player;
        },

        withPopper(dropdownList, component, {width},) {
            dropdownList.style.width = width;
            createPopper(component.$refs.toggle, dropdownList, {
                placement: 'top',
                modifiers: [
                {
                    name: 'offset', options: {
                    offset: [0, -1]
                    }
                },
                {
                    name: 'toggleClass',
                    enabled: true,
                    phase: 'write',
                    fn ({state}) {
                        component.$el.classList.toggle('drop-up', state.placement === 'top')
                    },
                }]
            });
        },

        onDragOver(squadID, evt) {
            this.dragTarget = squadID;
        },
        onDragEnter(squadID, evt) {
            this.dragTarget = squadID;

            const packedCode = evt.dataTransfer.types.find(t => t.startsWith('ally:'));

            if (packedCode) {
                const ally_code = packedCode.split(':')[1];
                const member = this.memberFor(ally_code);
                this.dropOK = this.memberAvailable(member, squadID);
            }

        },
        onDragLeave() {
            this.dragTarget = null;
            this.dropOK = false;
        },
        onDrop(squadID, evt) {
            const ally_code = evt.dataTransfer.getData('text/plain');
            const member = this.memberFor(ally_code);

            if (this.memberAvailable(member, squadID)) {
                this.selectedMember[squadID] = member;
                this.addMember(squadID);
            }

            this.dragTarget = null;
            this.dropOK = false;
        },
    },
}
</script>

<style lang="scss" scoped>
@import "../../../sass/_variables.scss";
.player-info > td:first-of-type {
    width: 140px;

    > div {
        padding: 2px 4px;
    }
}

.card-body {
    padding: 15px 0;

    > * {
        padding-left: 15px;
        padding-right: 15px;
    }
}

.zone-member-wrapper {
    overflow-x: visible !important;
    table:first-of-type, .add-member {
        margin-top: 4px;
    }
}

.targetable {
    padding: 15px;
    border-radius: 8px;
}

.over.targetable {
    background: $sw-yellow;
    &.not-dropable {
        background: $red;
        cursor: not-allowed;
    }
}

.name-wrapper, .squad-wrapper {
    position: relative;

    > button.btn-danger {
        position: absolute;
        left: -2px;
        top: -2px;
        opacity: 0;
        transition: opacity 300ms ease-out;
        background: white;
        border-radius: 50%;
        padding: 0px;
        box-shadow: 0px 0px 2px black;
    }

    &:hover {
        > button.btn-danger {
            opacity: 1;
        }
    }
}

.card-body {
    position: relative;
}

.modal-mask {
    position: absolute;
}
</style>

<style lang="scss">
@import "../../../sass/_variables.scss";
.player-info {
    .characters {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
}
.v-select.drop-up.vs--open .vs__dropdown-toggle {
    border-radius: 0 0 4px 4px;
    border-top-color: transparent;
    border-bottom-color: rgba(60, 60, 60, 0.26);
}

[data-popper-placement='top'] {
    border-radius: 4px 4px 0 0;
    border-top-style: solid;
    border-bottom-style: none;
    box-shadow: 0 -3px 6px rgba(0, 0, 0, 0.15)
}

.tooltip-row.squad-row td > div {
    padding: 2px;
}

.notes-field {
    border: 1px solid $gray-300;
    background: $white;
    border-radius: 4px;
    color: $gray-900;
    margin: 0 0 16px;
    padding: 4px;
}
</style>