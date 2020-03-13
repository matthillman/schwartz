<template>
    <div class="card-body">
        <div class="row no-margin">
            <div class="col-8">
                <div class="row no-margin justify-content-between align-items-baseline">
                    <h2>Zone Config</h2>
                    <button class="btn btn-secondary btn-image with-text" @click="showSendDialog">
                        <ion-icon name="send" size="small"></ion-icon>
                        <span>Send Messages</span>
                    </button>
                </div>

                <div class="column">
                    <div class="row justify-content-center no-margin">
                        <div v-for="(slice, index) in [[8, 9, 10], [5, 6, 7], [3, 4], [1, 2]]" :key="index" class="column no-margin zone-wrapper">
                            <div v-for="zone in slice" :key="zone"
                                class="zone" :class="[`zone-${zone}`]"
                                @click="currentZone = zone"
                            >
                                <img :src="`/images/tw/defense-zone-${zone}.png`">
                                <div class="zone-content-wrapper" :class="{active: currentZone == zone, 'member-highlight': hasTeaminZone(zone, highlightMember) }">
                                    <div class="column justify-content-center align-items-center">
                                        <h1>{{ zone }}</h1>
                                        <div>{{ getTeamsInZone(zone) }} {{ getTeamsInZone(zone) == 1 ? 'team' : 'teams' }}</div>
                                        <div class="row no-margin justify-content-center align-items-start">
                                            <div class="column char-image-column" v-for="leader_id in getLeadersForZone(zone)" :key="leader_id">
                                                <div class="char-image-square small" :class="[units[leader_id].alignment]">
                                                    <img :src="`/images/units/${leader_id}.png`">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <button class="btn btn-primary overview-button" @click="currentZone = 0">Overview</button> -->
                </div>

                <page-view :first-page="1" :last-page="10" :current-page="currentZone" ref="pageContainer">
                    <template #page="{ page }">
                        <div class="card-header">
                            <div v-if="page > 0" class="row no-margin justify-content-between align-items-baseline">
                                <h3>Zone {{ page }}</h3>
                                <div>{{ getTeamsInZone(page) }} {{ getTeamsInZone(page) == 1 ? 'team' : 'teams' }} assigned</div>
                            </div>
                            <h3 v-else>Overview</h3>
                        </div>
                        <tw-zone
                            :ref="`zone_${page}`"
                            v-if="page > 0"
                            :zone="page"
                            :zone-data="getPlanForZone(page)"
                            :notes="getNotesForZone(page)"
                            :squads="squads"
                            :units="units"
                            :members="ourMembers"
                            :drag-mode="!!draggingMember"
                            @add-squad="addSquad"
                            @remove-squad="(z, s) => confirmDeleteSquad = {z, s}"
                            @add-member="addMember"
                            @remove-member="deleteMember"
                            @update-notes="updateNotes"
                            @add-multiple="addMultipleForZone"
                        ></tw-zone>
                        <div class="card-body" v-else>
                        </div>
                    </template>
                </page-view>
            </div>

            <div class="col-4">
                <collapsable start-open>
                    <template #top-trigger="{ open }">
                        <div class="row no-margin align-items-start">
                            <ion-icon :name="open ? `chevron-down` : `chevron-forward`" size="medium"></ion-icon>
                            <h4>Squads</h4>
                        </div>
                    </template>
                    <mini-squad-table v-for="squad in squads" :key="squad.id"
                        :squad="squad"
                        :units="units"
                        no-header
                        :max-units="5"
                    ></mini-squad-table>
                </collapsable>

                <div class="defense-list">
                    <div class="stat-list column">
                        <div class="row justify-content-between align-items-baseline stat-header">
                            <div>Member</div>
                            <div>Banners</div>
                        </div>
                        <a :href="`/twp/${plan.id}/member/${member.ally_code}`"
                            class="row justify-content-between"
                            :class="{ dragging: draggingMember == member.ally_code }"
                            v-for="member in ourMembers"
                            :key="member.bannerKey"
                            draggable="true"
                            @dragstart.self="onDragStart(member, $event)"
                            @dragend.self="onDragEnd"
                            @mouseenter="overMember(member)"
                            @mouseleave="leaveMember(member)"
                        >
                            <div>{{ member.player }}</div>
                            <div>{{ member.bannerCount }}</div>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <modal v-if="addMultiple" @close="addMultiple = null; potentialAddMembers = []" wider>
            <template #header><h3>Multiple Assignment</h3></template>
            <template #body>
                <member-filter
                    :squad="squads[addMultiple]"
                    :members="availableMembersFor(addMultipleZone, addMultiple)"
                    :units="units"
                    :max="25 - getTeamsInZone(addMultipleZone)"
                    @changed="potentialAddMembers = $event"></member-filter>
            </template>
            <template #footer>
                <button class="btn btn-primary" :disabled="!potentialAddMembers.length" @click="addMember(addMultipleZone, addMultiple, potentialAddMembers)">Assign Members</button>
            </template>
        </modal>


        <modal v-if="confirmDeleteSquad" @close="confirmDeleteSquad = null">
            <template #header><h3>Are you sure you want to delete this squad?</h3></template>
            <template #body>
                <div>
                    This will remove this squad and all its assigned teams from this zone. <strong>This cannot be undone</strong>. Continue?
                </div>
            </template>
            <template #footer>
                <button class="btn btn-danger" @click="deleteSquad(confirmDeleteSquad.z, confirmDeleteSquad.s)">Delete it</button>
            </template>
        </modal>

        <modal v-if="sendMessages" @close="sendMessages = null">
            <template #header><h3>Send Assignment Messages</h3></template>
            <template #body>
                <div>
                    Send DMs to the following:
                </div>
                <div class="small-note">(Only showing members with assignments)</div>
                <div class="small-note">(Will only acutally send DMs to accounts with mapped Discord Users)</div>
                <div class="row no-margin justify-content-end">

                    <button class="btn btn-primary btn-icon with-text inverted" @click="membersToMessage = members.filter(m => m.bannerCount).map(m => m.ally_code)">
                        <ion-icon name="checkbox" size="small"></ion-icon>
                        <span>Select All</span>
                    </button>
                    <button class="btn btn-primary btn-icon with-text inverted" @click="membersToMessage = []">
                        <ion-icon name="square" size="small"></ion-icon>
                        <span>Select None</span>
                    </button>
                </div>
                <div class="checkbox-list-wrapper">
                    <div v-for="member in members.filter(m => m.bannerCount)" :key="member.ally_code">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" v-model="membersToMessage" :value="member.ally_code" :id="`message-${member.ally_code}`">
                            <label class="form-check-label" :for="`message-${member.ally_code}`">{{ member.player }}</label>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button class="btn btn-primary" @click="sendDMs">Send DMs</button>
            </template>
        </modal>
    </div>
</template>

<script>
export default {
    components: {
        'member-filter': require('./MemberFilter.vue').default,
    },
    props: {
        plan: Object,
        squads: Object,
        units: Object,
        members: Array,
    },
    mounted() {
        this.updateBannerCount();
        this.updateMemberSquadCount();
    },
    data() {
        return {
            currentZone: 0,
            ourPlan: this.plan,
            ourMembers: this.members,
            draggingMember: null,
            addMultiple: null,
            addMultipleZone: null,
            potentialAddMembers: [],
            confirmDeleteSquad: null,
            highlightMember: null,
            sendMessages: null,
            membersToMessage: [],
        };
    },
    methods: {
        memberDifference(squad) {
            return Math.max(0, Object.values(this.squads).reduce((total, s) => Math.max(total, Math.min(s.additional_members.length, 4)), 0) - squad.additional_members.length);
        },

        alignment(char) {
            return (this.units[char] || { alignment: 'neutral' }).alignment;
        },

        updateMemberSquadCount() {
            for (const index of [...Array(10).keys()]) {
                const zone = index + 1;
                const p = this.getPlanForZone(zone);
                for (const squadID in p) {
                    const members = p[squadID];
                    for (const ally_code of members) {
                        const member = this.ourMembers.find(m => m.ally_code == ally_code);
                        const memberSquads = member.usedSquads || new Set;
                        memberSquads.add(this.squads[squadID].leader_id);
                        member.usedSquads = memberSquads;
                    }
                }
            }
        },
        updateBannerCount() {
            for (const member of this.ourMembers) {
                member.bannerCount = this.getBannerCount(member);
                member.bannerKey = `${member.ally_code}-${member.bannerCount}`;
            }

            this.$forceUpdate();
        },
        nameForMember(ally_code) {
            const member = this.ourMembers.find(m => m.ally_code == ally_code);
            return (member || {player: 'LEEROY JENKINS'}).player;
        },

        getBannerCount(member) {
            let total = 0;
            for (const index of [...Array(10).keys()]) {
                const zone = index + 1;
                const teamsInZone = Object.values(this.getPlanForZone(zone))
                    .map(z => z.filter(m => m === member.ally_code).length)
                    .reduce((t, c) => t + c, 0)

                total += teamsInZone * ([5, 8].includes(zone) ? 34 : 30);
            }

            return total;
        },

        getPlanForZone(zone) {
            return this.ourPlan[`zone_${zone}`] || {};
        },
        getNotesForZone(zone) {
            return this.ourPlan[`zone_${zone}_notes`] || '';
        },
        getTeamsInZone(zone) {
            return Object.values(this.getPlanForZone(zone)).flat().length;
        },
        hasTeaminZone(zone, ally_code) {
            return ally_code !== null && Object.values(this.getPlanForZone(zone)).flat().includes(ally_code);
        },
        getLeadersForZone(zone) {
            return Array.from(new Set(Object.keys(this.getPlanForZone(zone)).map(s => this.squads[s].leader_id)));
        },

        addSquad(zone, squadID) {
            if (!this.getPlanForZone(zone)[squadID]) {
                this.getPlanForZone(zone)[squadID] = [];
                this.saveData(zone);
            }
        },
        deleteSquad(zone, squadID) {
            delete this.getPlanForZone(zone)[squadID];
            this.saveData(zone);
            this.$refs[`zone_${zone}`].$forceUpdate();
            this.confirmDeleteSquad = null;
        },

        addMember(zone, squadID, members) {
            let dirty = false;
            if (!Array.isArray(members)) {
                members = [members.ally_code];
            }
            for (const ally_code of members) {
                if (!this.getPlanForZone(zone)[squadID].includes(ally_code)) {
                    this.getPlanForZone(zone)[squadID].push(ally_code);
                    this.getPlanForZone(zone)[squadID].sort((a, b) => this.nameForMember(a).localeCompare(this.nameForMember(b)));
                    dirty = true;
                }
            }

            if (dirty) {
                this.saveData(zone);
            }

            if (this.addMultiple) {
                this.addMultiple = null;
                this.addMultipleZone = null;
                this.potentialAddMembers = [];
            }
        },
        deleteMember(zone, squadID, member) {
            const index = this.getPlanForZone(zone)[squadID].indexOf(member.ally_code);
            this.getPlanForZone(zone)[squadID].splice(index, 1);
            this.saveData(zone);
        },

        updateNotes(zone, notes) {
            this.ourPlan[`zone_${zone}_notes`] = notes;
            this.saveData(zone);
        },

        addMultipleForZone(zone, squadID) {
            this.addMultipleZone = zone;
            this.addMultiple = squadID;
        },
        memberAvailable(member, zone, squadID) {
            return !this.getPlanForZone(zone)[squadID].includes(member.ally_code)
            && (!member.usedSquads || !member.usedSquads.has(this.squads[squadID].leader_id));
        },
        availableMembersFor(zone, squadID) {
            return this.members.filter(m => this.memberAvailable(m, zone, squadID));
        },

        onDragStart(member, evt) {
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.setData('text/plain', member.ally_code);
            evt.dataTransfer.setData(`ally:${member.ally_code}`, '');
            this.draggingMember = member.ally_code;
            setTimeout(() => this.$refs.pageContainer.$el.scrollIntoView(true), 100);
        },
        onDragEnd() {
            this.draggingMember = null;
        },

        overMember(member) {
            this.highlightMember = member.ally_code;
        },
        leaveMember(member) {
            if (this.highlightMember == member.ally_code) {
                this.highlightMember = null;
            }
        },

        showSendDialog() {
            this.membersToMessage = this.members.filter(m => m.bannerCount).map(m => m.ally_code);
            this.sendMessages = true;
        },
        async sendDMs() {
            console.warn(this.membersToMessage);
            try {
                await axios.post(`/twp/${this.ourPlan.id}/dm`, {
                    members: this.membersToMessage.join(','),
                });
                alert("DMs queued to be sent");
                this.sendMessages = null;
                this.membersToMessage = [];
            } catch (error) {
                console.error(error);
            }
        },

        async saveData(zone) {
            try {
                await axios.put(`/twp/${this.ourPlan.id}/${zone}`, {
                    assignments: JSON.stringify(this.getPlanForZone(zone)),
                    notes: this.ourPlan[`zone_${zone}_notes`],
                });

                this.updateBannerCount();
                this.updateMemberSquadCount();
            } catch (error) {
                console.error(error);
            }

        },
    }
}
</script>

<style lang="scss" scoped>
.defense-list {
    margin-top: 16px;
}
.overview-button {
    margin: 8px 0;
}
.dragging {
    transform: scale(0.9);
    opacity: 0.6;
}
</style>

<style lang="scss">
.extra-units {
    padding: 2px;
}

.dragging .page-wrapper {
    position: sticky;
    top: 8px;
}

.checkbox-list-wrapper {
    height: calc(100% - 20px);
    max-height: 280px;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;

    > div {
        scroll-snap-align: start;
        position: relative;
        background: #f8f9fa;
        padding: 4px;
        border-radius: 8px;
        margin-bottom: 4px;
        cursor: pointer;

        label {
            cursor: pointer;
            width: 100%;
        }
    }
}
</style>