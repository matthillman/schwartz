<template>
<div class="card">
    <div class="card-header">
        <div class="row no-margin justify-content-between align-items-baseline">
            <h3>Zone {{ zone }}</h3>
            <div>{{ Object.values(ourData).flat().length }} teams assigned</div>
            <button class="btn btn-primary" @click="saveData">Save</button>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group row no-margin align-items-start">
            <v-select
                class="grow"
                :options="availableSquads()"
                placeholder="Select Squad"
                v-model="selectedSquad"
                :label="'display'"
            >
                <template v-slot:option="squad">
                    <mini-squad-table :squad="squad" :units="units"></mini-squad-table>
                </template>
                <template v-slot:selected-option="squad">
                    <mini-squad-table :squad="squad" :units="units"></mini-squad-table>
                </template>
            </v-select>
            <button class="btn btn-primary" @click="addSquad">Add to Zone</button>
        </div>
    </div>

    <div class="card-body" v-for="(zoneMembers, squadID) in ourData" :key="squadID">
        <mini-squad-table :squad="squads[squadID]" :units="units"></mini-squad-table>

        <div v-for="member of zoneMembers" :key="member.ally_code">{{ member.player }}</div>

        <div class="form-group row no-margin align-items-start">
            <v-select
                class="grow"
                :options="availableMembers(squadID)"
                placeholder="Add Member"
                v-model="selectedMember[squadID]"
                :label="'player'"
            >
            </v-select>
            <button class="btn btn-primary" @click="addMember(squadID)">Add</button>
        </div>
    </div>
</div>
</template>

<script>
export default {
    props: {
        zone: Number,
        zoneData: Object|Array,
        squads: Object,
        units: Object,
        members: Array,
    },
    mounted() {
        if (!Array.isArray(this.zoneData)) {
            this.ourData = this.zoneData;
        }
    },
    data() {
        return {
            selectedSquad: null,
            ourData: {},
            selectedMember: {},
        };
    },
    methods: {
        addSquad() {
            if (!this.ourData[this.selectedSquad.id]) {
                this.ourData[this.selectedSquad.id] = [];
            }
            this.selectedSquad = null;
        },
        addMember(squadID) {
            if (!this.ourData[squadID].map(m => m.ally_code).includes(this.selectedMember[squadID].ally_code)) {
                this.ourData[squadID].push(this.selectedMember[squadID]);
                this.ourData[squadID].sort((a, b) => a.player.localeCompare(b.player));
                this.selectedMember[squadID] = null;
            }
        },
        availableSquads() {
            const usedSquads = Object.keys(this.ourData).map(id => parseInt(id));
            return Object.values(this.squads).filter(s => !usedSquads.includes(s.id));
        },
        availableMembers(squadID) {
            const usedMembers = this.ourData[squadID].map(m => m.ally_code);
            return this.members.filter(m => !usedMembers.includes(m.ally_code));
        },
        saveData() {

        }
    },
}
</script>