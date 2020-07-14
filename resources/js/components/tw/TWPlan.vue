<template>
    <div class="card-body">
        <div class="column justify-content-start align-items-stretch portrait-row" :class="{open: userList.length > 1}" v-show-slide="userList.length > 1">
            <div class="row no-margin justify-content-end align-items-start"><div class="small-note">Also Editing this Plan</div></div>
            <div class="row no-margin justify-content-end align-items-start">
                <div class="user-portrait-wrapper" v-for="user in userList" :key="user.id">
                    <tooltip>
                        <div class="user-portrait">
                            <img :src="user.avatar">
                        </div>
                        <template #tooltip>
                            <div class="user-name">{{ user.name }}</div>
                        </template>
                    </tooltip>
                </div>
            </div>
        </div>

        <div class="column align-items-center justify-content-center" v-if="!sortedSquads.length">
            <h2>Loading Plan Data…</h2>
            <loading-indicator></loading-indicator>
            <div class="small-note">(can take up to 30s)</div>
        </div>
        <div class="row no-margin" v-if="sortedSquads.length">
            <div class="col-8">
                <div class="row no-margin justify-content-between align-items-baseline">
                    <h2>Zone Config</h2>
                    <button class="btn btn-secondary btn-image with-text striped" @click="showSendDialog">
                        <ion-icon name="send" size="small"></ion-icon>
                        <span>Send Messages</span>
                    </button>
                </div>

                <div class="column">
                    <div class="row justify-content-center no-margin" ref="zoneContainer">
                        <div v-for="(slice, index) in [[8, 9, 10], [5, 6, 7], [3, 4], [1, 2]]" :key="index" class="column no-margin zone-wrapper">
                            <div v-for="zone in slice" :key="zone"
                                class="zone drop-target"
                                :class="[`zone-${zone}`, { over: dragTarget == zone && !draggingMember, 'not-dropable': !dropOK && !draggingMember }]"
                                @click="currentZone = zone"
                                @dragover.prevent="onDragOver(zone, $event)"
                                @dragenter="onDragEnter(zone, $event)"
                                @dragleave.self="onDragLeave(zone)"
                                @drop.prevent.stop="onDrop(zone, $event)"
                                @dragend="onDragLeave(zone)"
                            >
                                <img :src="`/images/tw/defense-zone-${zone}.png`">
                                <div class="zone-content-wrapper" :class="{active: currentZone == zone, 'member-highlight': hasTeaminZone(zone, highlightMember) }">
                                    <div class="column justify-content-center align-items-center">
                                        <div class="row no-margin justify-content-end align-items-start zone-portraits" v-show-slide="userList.length > 1">
                                            <div class="user-portrait-wrapper mini" v-for="user in userList.filter(u => u.zone == zone)" :key="user.id">
                                                <tooltip>
                                                    <div class="user-portrait">
                                                        <img :src="user.avatar">
                                                    </div>
                                                    <template #tooltip>
                                                        <div class="user-name">{{ user.name }} ({{ user.zone }})</div>
                                                    </template>
                                                </tooltip>
                                            </div>
                                        </div>
                                        <h1>{{ zone }}</h1>
                                        <div>{{ getTeamsInZone(zone) }} {{ getTeamsInZone(zone) == 1 ? 'team' : 'teams' }}</div>
                                        <div class="row no-margin justify-content-center align-items-start">
                                            <div class="column char-image-column" v-for="leader_id in getLeadersForZone(zone).filter(l => !!l)" :key="leader_id">
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
                        <div class="row no-margin justify-content-between align-items-center">
                            <div class="row no-margin align-items-start">
                                <ion-icon :name="open ? `chevron-down` : `chevron-forward`" size="medium"></ion-icon>
                                <h4>Squads</h4>
                            </div>
                            <div>
                                <a :href="`/guild/${plan.guild.id}/${plan.squad_group_id}/guild/0`" target="_blank"><ion-icon name="eye" size="medium"></ion-icon></a>
                                <a :href="`/squads?group=${plan.squad_group_id}`"><ion-icon name="pencil" size="medium"></ion-icon></a>
                            </div>
                        </div>
                    </template>
                    <mini-squad-table v-for="squad in sortedSquads" :key="squad.id"
                        :squad="squad"
                        :units="units"
                        no-header
                        :max-units="5"
                        draggable="true"
                        :style-class="{dragging: draggingSquad == squad.id}"
                        @dragstart="onDragStartSquad(squad, $event)"
                        @dragend="onDragEndSquad"
                    ></mini-squad-table>
                </collapsable>

                <div class="defense-list">
                    <div class="stat-list column">
                        <div class="row justify-content-between align-items-baseline stat-header">
                            <div class="row justify-content-between align-items-center">Member&nbsp;<ion-icon name="pencil" size="small" @click="selectMembers = true"></ion-icon></div>
                            <div>Banners</div>
                        </div>
                        <div :href="`/twp/${plan.id}/member/${member.ally_code}`"
                            :ref="`member_${member.ally_code}`"
                            class="row justify-content-between member-banners"
                            :class="{ dragging: draggingMember && draggingMember.ally_code == member.ally_code, duplicate: member.duplicates && member.duplicates.size }"
                            v-for="member in ourMembers"
                            :key="member.bannerKey"
                            draggable="true"
                            @dragstart.self="onMemberDragStart(member, $event)"
                            @dragend.self="onMemberDragEnd"
                            @mouseenter="overMember(member)"
                            @mouseleave="leaveMember(member)"
                        >
                            <div class="row no-margin justify-content-start align-items-center">
                                <a :href="`/twp/${plan.id}/member/${member.ally_code}`" target="_assignment"><ion-icon name="eye" size="small"></ion-icon></a>
                                <div>{{ member.player }}</div>
                                <tooltip v-if="member.duplicates && member.duplicates.size">
                                    <ion-icon name="warning" size="small" class="warning"></ion-icon>
                                    <template #tooltip>
                                        <div v-if="member.duplicates && member.duplicates.size">
                                            <mini-squad-table :squad="squadifyDupes(member)" :units="units" flex-width></mini-squad-table>
                                        </div>
                                    </template>
                                </tooltip>
                            </div>
                            <div>{{ member.bannerCount }}</div>
                        </div>
                    </div>
                </div>

                <drop-target
                    :anchor="dragMemberRef"
                >
                    <div class="column align-content-center drop-wrapper">
                        <h4>Drop to Assign</h4>
                        <div
                            v-for="(members, squadID) in getPlanForZone(currentZone)" :key="squadID"
                            class="drop-target"
                            :class="{ over: dragTarget == squadID && draggingMember, targetable: draggingMember, 'not-dropable': !dropOK && draggingMember }"
                            @dragover.prevent="onMemberDragOver(squadID, $event)"
                            @dragenter="onMemberDragEnter(squadID, $event)"
                            @dragleave.self="onMemberDragLeave"
                            @drop.prevent.stop="onMemberDrop(squadID, $event)"
                            @dragend="onMemberDragLeave"
                        >
                            <mini-squad-table :squad="squads[squadID]" :units="units" flex-width></mini-squad-table>
                        </div>
                    </div>
                </drop-target>

            </div>
        </div>

        <modal v-if="addMultiple" @close="addMultiple = null; potentialAddMembers = []" wider>
            <template #header><h3>Multiple Assignment</h3></template>
            <template #body>
                <member-filter
                    :squad="squads[addMultiple]"
                    :members="availableMembersFor(addMultipleZone, addMultiple)"
                    :units="units"
                    :max="Math.ceil(includedAllyCodes.length / 2) - getTeamsInZone(addMultipleZone)"
                    @changed="potentialAddMembers = $event"></member-filter>
            </template>
            <template #footer>
                <button class="btn btn-primary striped" :disabled="!potentialAddMembers.length" @click="addMember(addMultipleZone, addMultiple, potentialAddMembers)"><span>Assign Members</span></button>
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
                <button class="btn btn-danger striped" @click="deleteSquad(confirmDeleteSquad.z, confirmDeleteSquad.s)"><span>Delete it</span></button>
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
                            <status :status="getStatusFor(member.dm_status)"></status>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <button class="btn btn-primary striped" @click="sendDMs"><span>Send DMs</span></button>
            </template>
        </modal>

        <modal v-if="selectMembers" @close="selectMembers = null">
            <template #header><h3>Active Members</h3></template>
            <template #body>
                <div>
                    The members active for this TW are:
                </div>
                <div class="row no-margin justify-content-end">

                    <button class="btn btn-primary btn-icon with-text inverted" @click="includedAllyCodes = members.map(m => m.ally_code)">
                        <ion-icon name="checkbox" size="small"></ion-icon>
                        <span>Select All</span>
                    </button>
                    <button class="btn btn-primary btn-icon with-text inverted" @click="includedAllyCodes = []">
                        <ion-icon name="square" size="small"></ion-icon>
                        <span>Select None</span>
                    </button>
                </div>
                <div class="checkbox-list-wrapper">
                    <div v-for="member in members" :key="member.ally_code">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" v-model="includedAllyCodes" :value="member.ally_code" :id="`include-${member.ally_code}`">
                            <label class="form-check-label" :for="`include-${member.ally_code}`">{{ member.player }}</label>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <div>{{ includedAllyCodes.length }}/50</div>
            </template>
        </modal>
    </div>
</template>

<script>
export default {
    components: {
        'member-filter': require('./MemberFilter.vue').default,
        'drop-target': require('./DragTarget.vue').default,
    },
    props: {
        userId: Number,
        plan: Object,
        activeMembers: Array,
    },
    async mounted() {
        const squadResponse = await axios.get(`/squads/${this.plan.squad_group_id}/data`);
        this.squadData.data = squadResponse.data.squads;
        this.unitData.data = squadResponse.data.units;
        const memResponse = await axios.get(`/guild/${this.plan.guild.id}/members/data?units=${Object.keys(this.units).join(',')}`);
        this.members.push(... memResponse.data);
        this.includedAllyCodes.push(... (this.activeMembers.length ? this.activeMembers : this.members.map(m => m.ally_code)));

        this.ourMembers = this.members.filter(m => this.includedAllyCodes.includes(m.ally_code));
        this.sortedSquads = Object.values(this.squads).sort((a, b) => {
            const glList = ['GLREY', 'SUPREMELEADERKYLOREN'];
            const metaList = ['GENERALSKYWALKER', 'JEDIKNIGHTREVAN', 'DARTHREVAN', 'GRIEVOUS', 'PADMEAMIDALA'];
            const aIsGL = glList.includes(a.leader_id);
            const bIsGL = glList.includes(b.leader_id);

            if (aIsGL && !bIsGL) {
                 return -1;
            } else if (!aIsGL && bIsGL) {
                return 1;
            }

            const aIsMeta = metaList.includes(a.leader_id);
            const bIsMeta = metaList.includes(b.leader_id);

            if (aIsMeta && !bIsMeta) {
                 return -1;
            } else if (!aIsMeta && bIsMeta) {
                return 1;
            }

            const aIsShips = this.units[a.leader_id].combat_type == 2;
            const bIsShips = this.units[b.leader_id].combat_type == 2;

            if (aIsShips && !bIsShips) {
                 return 1;
            } else if (!aIsShips && bIsShips) {
                return -1;
            }

            return a.leader_id.localeCompare(b.leader_id);
        });
        for (const index of [...Array(10).keys()]) {
            const zone = index + 1;
            let plan = this.getPlanForZone(zone)

            for (const squad in plan) {
                if (!this.squads[squad]) {
                    delete plan[squad];
                }
            }
        }
        Echo.join(`plan.${this.plan.id}`)
            .here(users => {
                this.userList = users;
                this.userList.sort((a, b) => a.name.localeCompare(b.name))
                this.user = this.userList.find(u => u.id == this.userId);
                this.user.zone = this.currentZone;
            })
            .joining(user => {
                const index = this.userList.findIndex(u => u.id == user.id);
                if (index === -1) {
                    this.userList.push(user);
                } else {
                    this.userList[index] = user;
                }
                this.userList.sort((a, b) => a.name.localeCompare(b.name));

                this.pushUserState();
            })
            .leaving(user => {
                const index = this.userList.findIndex(u => u.id == user.id);
                this.userList.slice(index, 1);
                this.userList.sort((a, b) => a.name.localeCompare(b.name))
            })
            .listen('.plan.changed', event => {
                if (event.zone > 0) {
                    this.updateData(event.zone, event.change);
                } else {
                    this.includedAllyCodes = JSON.parse(event.change.members);
                }
            })
            .listen('.user.changed', e => {
                this.userList.find(u => u.id == e.user.id).zone = e.user.zone;
                this.$forceUpdate();
            })
            .listen('.member.dm.status', e => {
                this.ourMembers.find(m => m.ally_code == e.member.ally_code).dm_status = e.member.dm_status;
                this.$forceUpdate();
            })
        ;
        this.updateBannerCount();
        this.updateMemberSquadCount();
    },
    data() {
        return {
            currentZone: 1,
            ourPlan: this.plan,
            sortedSquads: [],
            includedAllyCodes: [],
            ourMembers: [],
            draggingMember: null,
            dragMemberRef: null,
            draggingSquad: null,
            addMultiple: null,
            addMultipleZone: null,
            potentialAddMembers: [],
            confirmDeleteSquad: null,
            highlightMember: null,
            sendMessages: null,
            selectMembers: null,
            membersToMessage: [],
            user: {},
            userList: [],
            dragTarget: null,
            dropOK: false,

            squadData: {
                data: {},
            },
            unitData: {
                data: {},
            },
            members: [],
        };
    },
    computed: {
        squads() {
            return this.squadData.data;
        },
        units() {
            return this.unitData.data;
        }
    },
    watch: {
        currentZone() {
            this.user.zone = this.currentZone;
            this.pushUserState();
        },
        selectMembers() {
            if (this.selectMembers !== null) {
                return;
            }

            if (this.includedAllyCodes.length == 0) {
                alert("You must have at least one member in the plan");

                this.selectMembers = true;
                return;
            }
            this.ourMembers = this.members.filter(m => this.includedAllyCodes.includes(m.ally_code));

            this.members.filter(m => !this.includedAllyCodes.includes(m.ally_code)).forEach(m => {
                m.bannerCount = 0;
                for (const index of [...Array(10).keys()]) {
                    for (const squadID in this.getPlanForZone(index + 1)) {
                        this.deleteMember(index + 1, squadID, m, true);
                    }

                    this.saveData(index + 1);
                }
            });

            this.saveMembers();
        },
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
                        if (!member) { continue; }
                        const memberSquads = member.usedSquads || new Set;
                        memberSquads.add(this.squads[squadID].leader_id);
                        member.usedSquads = memberSquads;
                        member.duplicates = this.getDuplicatesSet(member);
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
        getDuplicatesSet(member) {
            let dupes = new Set;
            let used = new Set;
            for (const index of [...Array(10).keys()]) {
                const zone = index + 1;
                const plan = this.getPlanForZone(zone);
                Object.keys(plan)
                    .filter(s =>  plan[s].find(m => m === member.ally_code) !== undefined)
                    .map(squad => [this.squads[squad].leader_id, ...this.squads[squad].additional_members])
                    .forEach(unitList => unitList.forEach(unit =>  used.has(unit) ? dupes.add(unit) : used.add(unit)));
            }

            return dupes;
        },
        squadifyDupes(member) {
            if (member.duplicates && member.duplicates.size) {
                let asArray = [...member.duplicates];

                return {display: "Duplicate Assignments", leader_id: asArray[0], additional_members: asArray.slice(1)};
            }
            return {leader_id: 'LEEROY', additional_members: []};
        },

        getPlanForZone(zone) {
            let plan = this.ourPlan[`zone_${zone}`] || {};

            return plan;
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
            return Array.from(new Set(Object.keys(this.getPlanForZone(zone)).map(s => this.squads[s] ? this.squads[s].leader_id : null)));
        },

        addSquad(zone, squadID) {
            if (!this.getPlanForZone(zone)[squadID]) {
                this.getPlanForZone(zone)[squadID] = [];
                this.saveData(zone);
            }
        },
        deleteSquad(zone, squadID) {
            const members = this.getPlanForZone(zone)[squadID];
            for (const ally_code of members) {
                const member = this.ourMembers.find(m => m.ally_code == ally_code);
                if (!member) { continue; }
                const memberSquads = member.usedSquads || new Set;
                memberSquads.delete(this.squads[squadID].leader_id);
                member.usedSquads = memberSquads;
            }
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
                    this.$refs[`zone_${zone}`].$forceUpdate();
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
        deleteMember(zone, squadID, member, skipSave) {
            const index = this.getPlanForZone(zone)[squadID].indexOf(member.ally_code);
            if (index > -1) {
                this.getPlanForZone(zone)[squadID].splice(index, 1);

                const memberSquads = member.usedSquads || new Set;
                memberSquads.delete(this.squads[squadID].leader_id);
                member.usedSquads = memberSquads;

                if (!skipSave) {
                    this.saveData(zone);
                }
            }
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
            return this.ourMembers.filter(m => this.memberAvailable(m, zone, squadID));
        },

        onMemberDragStart(member, evt) {
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.setData('text/plain', member.ally_code);
            evt.dataTransfer.setData(`ally:${member.ally_code}`, '');
            this.draggingMember = member;
            this.dragMemberRef = evt.target;
        },
        onMemberDragEnd() {
            this.draggingMember = null;
            this.dragMemberRef = null;
        },
        onMemberDragOver(squadID, evt) {
            this.dragTarget = squadID;
        },
        onMemberDragEnter(squadID, evt) {
            this.dragTarget = squadID;

            const packedCode = evt.dataTransfer.types.find(t => t.startsWith('ally:'));

            if (packedCode) {
                this.dropOK = this.memberAvailable(this.draggingMember, this.currentZone, squadID);
            }
        },
        onMemberDragLeave() {
            this.dragTarget = null;
            this.dropOK = false;
        },
        onMemberDrop(squadID, evt) {
            this.addMember(this.currentZone, squadID, this.draggingMember);

            this.dragTarget = null;
            this.dropOK = false;
        },

        onDragStartSquad(squad, evt) {
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.setData('text/plain', squad.id);
            evt.dataTransfer.setData(`squad:${squad.id}`, '');
            this.draggingSquad = squad.id;
            if (!this.isInViewport(this.$refs.zoneContainer)) {
                setTimeout(() => this.$refs.zoneContainer.scrollIntoView(true), 100);
            }
        },
        onDragEndSquad() {
            this.draggingSquad = null;
            this.dragTarget = null;
            this.dropOK = false;
        },

        onDragOver(zone, evt) {
            if (!this.draggingSquad) { return; }
            this.dragTarget = zone;
        },
        onDragEnter(zone, evt) {
            if (!this.draggingSquad) { return; }
            this.dragTarget = zone;

            const packedCode = evt.dataTransfer.types.find(t => t.startsWith('squad:'));

            if (packedCode) {
                const squadID = packedCode.split(':')[1];

                this.dropOK = !Object.keys(this.getPlanForZone(zone)).includes(squadID) && !!this.squads[squadID] && (
                    (this.units[this.squads[squadID].leader_id].combat_type == 2) == [5, 8].includes(zone)
                );
            }

        },
        onDragLeave(zone) {
            if (this.dragTarget == zone) {
                this.dragTarget = null;
                this.dropOK = false;
            }
        },
        onDrop(zone, evt) {
            const squadID = evt.dataTransfer.getData('text/plain');

            if (!Object.keys(this.getPlanForZone(zone)).includes(squadID) && !!this.squads[squadID]) {
                this.addSquad(zone, squadID);
                this.$refs[`zone_${zone}`].$forceUpdate();
            }

            this.dragTarget = null;
            this.dropOK = false;
        },

        overMember(member) {
            this.highlightMember = member.ally_code;
        },
        leaveMember(member) {
            if (this.highlightMember == member.ally_code) {
                this.highlightMember = null;
            }
        },

        isInViewport(elem) {
            const bounding = elem.getBoundingClientRect();
            return (
                bounding.top >= 0 &&
                bounding.left >= 0 &&
                bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        showSendDialog() {
            this.membersToMessage = this.members.filter(m => m.bannerCount).map(m => m.ally_code);
            this.sendMessages = true;
        },
        getStatusFor(dmStatus) {
            switch(dmStatus) {
                case -1: return 'failed';
                case 1: return 'pending';
                case 2: return 'completed';
                default: return '';
            }
        },
        async sendDMs() {
            try {
                await axios.post(`/twp/${this.ourPlan.id}/dm`, {
                    members: this.membersToMessage.join(','),
                });
                alert("DMs queued to be sent");
                this.membersToMessage = [];
            } catch (error) {
                console.error(error);
            }
        },

        updateData(zone, change) {
            this.ourPlan[`zone_${zone}`] = JSON.parse(change.assignments);
            this.ourPlan[`zone_${zone}_notes`] = change.notes;

            this.$refs[`zone_${zone}`].$forceUpdate();
            this.updateBannerCount();
            this.updateMemberSquadCount();
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
        async saveMembers() {
            try {
                await axios.put(`/twp/${this.ourPlan.id}/members`, {
                    members: JSON.stringify(this.includedAllyCodes),
                });
            } catch (error) {
                console.error(error);
            }

        },
        async pushUserState() {
            try {
                if (this.userList.length > 1) {
                    await axios.put(`/twp/${this.ourPlan.id}/user`, {
                        id: this.user.id,
                        zone: this.currentZone,
                    });
                }

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
@import "../../../sass/_variables.scss";
.row {
    > .col-8, > .col-4 {
        &:first-child {
            padding-left: 0;
        }
        &:last-child {
            padding-right: 0;
        }
    }
}
.defense-list {
    margin-top: 16px;
}
.overview-button {
    margin: 8px 0;
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

.drop-wrapper {
    padding: 8px;

    > * {
        margin-bottom: 8px;

        &:last-child {
            margin-bottom: 0;
        }
    }
    h4 {
        text-align: center;
    }
}

.member-banners {
    > div {
        > * {
            line-height: 1;
        }

        > * + * {
            margin-left: 4px;
        }
    }
}

.duplicate {
    position: relative;
    cursor: pointer;

    border: 2px solid $light-red;
    background: rgba($color: $dark-red, $alpha: 0.5) !important;
}
</style>

<style lang="scss">
.extra-units {
    padding: 2px;
}

.dragging {
    transform: scale(0.9);
    opacity: 0.6;
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

        .form-check {
            display: flex;
        }

        label {
            cursor: pointer;
            width: 100%;
        }

        &:hover {
            background: #ced4da;
        }
    }
}

.portrait-row {
    background: #e9ecef;
    box-shadow: inset 0px 0px 1px #495057;
    margin: 0 -15px;
    padding: 0 15px 4px;

    &.open {
        margin-top: -15px;
        margin-bottom: 15px;
    }
}

.user-portrait-wrapper {
    display: inline-block;
    margin: 0 4px;
    width: 40px;

    .user-portrait {
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: white;
        border-radius: 50%;
        border: 3px double #bfd5ff;
        box-shadow: 0 0 3px #0071d6;
        overflow: hidden;
        box-sizing: border-box;

        &, & img {
            width: 100%;
        }
    }

    .user-name {
        text-align: center;
    }

    &.mini {
        &, .user-portrait {
            width: 25px;
            height: 25px;

            &.user-portrait {
                border-width: 1px;
            }
        }
    }
}
.zone-portraits {
    position: absolute;
    top: 3px;
    right: 0;
}

</style>