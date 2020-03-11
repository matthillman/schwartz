<template>
    <div class="card-body">
        <div class="row no-margin">
            <div class="col-md-8">
                <div class="row no-margin justify-content-between align-items-baseline">
                    <h2>Zone Config</h2>
                    <button class="btn btn-secondary btn-image with-text">
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
                                <div class="zone-content-wrapper" :class="{active: currentZone == zone}">
                                    <div class="column justify-content-center align-items-center">
                                        <h1>{{ zone }}</h1>
                                        <div>{{ getTeamsInZone(zone) }} {{ getTeamsInZone(zone) == 1 ? 'team' : 'teams' }}</div>
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
                            v-if="page > 0"
                            :zone="page"
                            :zone-data="getPlanForZone(page)"
                            :notes="getNotesForZone(page)"
                            :squads="squads"
                            :units="units"
                            :members="ourMembers"
                            :drag-mode="!!draggingMember"
                            @add-squad="addSquad"
                            @remove-squad="deleteSquad"
                            @add-member="addMember"
                            @remove-member="deleteMember"
                            @update-notes="updateNotes"
                        ></tw-zone>
                        <div class="card-body" v-else>
                        </div>
                    </template>
                </page-view>
            </div>

            <div class="col-md-4">
                <collapsable start-open>
                    <template #top-trigger="{ open }">
                        <div class="row no-margin align-items-start">
                            <ion-icon :name="open ? `chevron-down` : `chevron-forward`" size="medium"></ion-icon>
                            <h4>Squads</h4>
                        </div>
                    </template>
                    <table class="squad-table micro">
                        <tbody>
                            <tr v-for="squad in squads" :key="squad.id" class="squad-row">
                                <td class="top" v-for="char_id in [squad.leader_id, ...squad.additional_members.slice(0, 3)]" :key="char_id">
                                    <div class="column char-image-column">
                                        <div class="char-image-square small" :class="[alignment(char_id)]">
                                            <img :src="`/images/units/${char_id}.png`">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                     <div v-if="squad.additional_members.length == 4" class="column char-image-column">
                                        <div class="char-image-square small" :class="[alignment(squad.additional_members[3])]">
                                            <img :src="`/images/units/${squad.additional_members[3]}.png`">
                                        </div>
                                    </div>
                                    <div v-else-if="squad.additional_members.length > 4" class="column justify-content-center align-items-center extra-units">
                                        <tooltip>
                                            +{{ squad.additional_members.length - 3 }}
                                            <template #tooltip>
                                                <table class="squad-table micro">
                                                    <tbody>
                                                        <tr class="squad-row tooltip-row">
                                                            <td v-for="char_id in squad.additional_members.slice(3)" :key="char_id">
                                                                <div class="column char-image-column">
                                                                    <div class="char-image-square small" :class="[alignment(char_id)]">
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
                                <td v-for="i in memberDifference(squad)" :key="i"><div>&nbsp;</div></td>
                            </tr>
                        </tbody>
                    </table>
                </collapsable>

                <div class="defense-list">
                    <div class="stat-list column">
                        <div class="row justify-content-between align-items-baseline stat-header">
                            <div>Member</div>
                            <div>Banners</div>
                        </div>
                        <a :href="`/twp/${plan.id}/${member.ally_code}`"
                            class="row justify-content-between"
                            :class="{ dragging: draggingMember == member.ally_code }"
                            v-for="member in ourMembers"
                            :key="member.bannerKey"
                            draggable="true"
                            @dragstart.self="onDragStart(member, $event)"
                            @dragend.self="onDragEnd"
                        >
                            <div>{{ member.player }}</div>
                            <div>{{ member.bannerCount }}</div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
export default {
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

        addSquad(zone, squadID) {
            if (!this.getPlanForZone(zone)[squadID]) {
                this.getPlanForZone(zone)[squadID] = [];
                this.saveData(zone);
            }
        },
        deleteSquad(zone, squadID) {
            delete this.getPlanForZone(zone)[squadID];
            this.saveData(zone);
        },

        addMember(zone, squadID, member) {
            if (!this.getPlanForZone(zone)[squadID].includes(member.ally_code)) {
                this.getPlanForZone(zone)[squadID].push(member.ally_code);
                this.getPlanForZone(zone)[squadID].sort((a, b) => this.nameForMember(a).localeCompare(this.nameForMember(b)));
                this.saveData(zone);
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
</style>