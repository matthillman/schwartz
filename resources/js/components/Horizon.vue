<script type="text/ecmascript-6">
    import moment from 'moment-timezone';

    export default {
        data() {
            return {
                ready: false,
                loadingNewEntries: false,
                hasNewEntries: false,
                page: 1,
                perPage: 50,
                totalPages: 1,
                jobs: [],
                tag: null,
            };
        },
        async mounted() {
            this.loadTags()
        },

        destroyed() {
            clearInterval(this.interval);
        },

        watch: {
            tag() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
                this.loadJobs();
                this.refreshJobsPeriodically();
            }
        },

        computed: {
            tabs() {
                return this.tags.map(tag => ({ title: tag.tag, index: tag.tag, tag: tag}))
            }
        },

        methods: {
            async loadTags() {
                this.ready = false;

                const response = await axios.get('/horizon/api/monitoring')
                this.tags = response.data;
                if (this.tags.length) {
                    this.tag = this.tags[0];
                } else {
                    this.tags = [
                        {tag: 'guild'},
                        {tag: 'mods'},
                        {tag: 'bot'},
                    ];
                    this.tag = this.tags[0];
                }
            },

            async loadJobs(starting = 0, refreshing = false) {
                if (!this.tag) { return; }
                if (!refreshing) {
                    this.ready = false;
                }

                // const response = await axios.get('/horizon/api/jobs/recent?starting_at=' + starting + '&limit=' + this.perPage)
                const response = await axios.get(`/horizon/api/monitoring/${encodeURIComponent(this.tag.tag)}?starting_at=${starting}&limit=${this.perPage}`)

                this.jobs = response.data.jobs;
                this.totalPages = Math.ceil(response.data.total / this.perPage);

                this.ready = true;
            },

            loadNewEntries() {
                this.jobs = [];
                this.loadJobs(0, false);
                this.hasNewEntries = false;
            },

            refreshJobsPeriodically() {
                this.interval = setInterval(() => {
                    if (this.page != 1) {
                        return;
                    }

                    this.loadJobs(0, true);
                }, 3000);
            },

            previous() {
                this.loadJobs(
                    (this.page - 2) * this.perPage
                );

                this.page -= 1;

                this.hasNewEntries = false;
            },

            next() {
                this.loadJobs(
                    this.page * this.perPage
                );

                this.page += 1;

                this.hasNewEntries = false;
            },

            tabChanged(tab) {
                this.tag = tab.tag;
            },

            jobBaseName(name) {
                if (!name.includes('\\')) return name;

                var parts = name.split('\\');

                return parts[parts.length - 1];
            },

            formatDate(unixTime) {
                return moment(unixTime * 1000).add(new Date().getTimezoneOffset() / 60);
            },

            readableTimestamp(timestamp) {
                return this.formatDate(timestamp).format('YYYY-MM-DD HH:mm:ss');
            },
        }
    }
</script>

<template>
    <div>
        <div v-if="!ready"
                class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                <path
                    d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
            </svg>

            <span>Loading...</span>
        </div>

        <tab-list
             v-if="ready && tabs.length > 0"
            :tabs="tabs"
            :selected="tag.tag"
            @changed="tabChanged"
        ></tab-list>

        <div v-if="ready && jobs.length == 0"
                class="d-flex flex-column align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
            <span>There aren't any jobs.</span>
        </div>

        <table v-if="ready && jobs.length > 0" class="table table-hover table-sm mb-0">
            <thead>
            <tr>
                <th>Job</th>
                <th>Queued At</th>
                <th>Runtime</th>
                <th class="text-right">Status</th>
            </tr>
            </thead>

            <tbody>
            <tr v-if="hasNewEntries" key="newEntries" class="dontanimate">
                <td colspan="100" class="text-center card-bg-secondary py-1">
                    <small><a href="#" v-on:click.prevent="loadNewEntries" v-if="!loadingNewEntries">Load New
                        Entries</a></small>

                    <small v-if="loadingNewEntries">Loading...</small>
                </td>
            </tr>

            <tr v-for="job in jobs" :key="job.id">
                <td>
                    <span v-if="job.status != 'failed'" :title="job.name">{{jobBaseName(job.name)}}</span>
                    <a v-if="job.status === 'failed'" :title="job.name" :href="'/horizon/Failed/' + job.id">{{ jobBaseName(job.name) }}</a>
                    <br>

                    <small class="text-muted">

                        <a :title="job.name" :href="'/horizon/recent-jobs/' + job.id">View Details</a>
                         | Queue: {{job.queue}}
                        <span v-if="job.payload.tags.length">
                            | Tags: {{ job.payload.tags && job.payload.tags.length ? job.payload.tags.slice(0,3).join(', ') : '' }}<span v-if="job.payload.tags.length > 3"> ({{ job.payload.tags.length - 3 }} more)</span>
                        </span>
                    </small>
                </td>
                <td class="table-fit">
                    {{ readableTimestamp(job.payload.pushedAt) }}
                </td>

                <td class="table-fit">
                    <span>{{ job.completed_at ? (job.completed_at - job.reserved_at).toFixed(2)+'s' : '-' }}</span>
                </td>

                <td class="text-right table-fit">
                    <svg v-if="job.status == 'completed'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                    </svg>

                    <svg v-if="job.status == 'reserved' || job.status == 'pending'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                    </svg>

                    <svg v-if="job.status == 'failed'" class="fill-danger" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                    </svg>
                </td>
            </tr>
            </tbody>
        </table>

        <div v-if="ready && jobs.length" class="p-3 d-flex justify-content-between border-top">
            <button @click="previous" class="btn btn-secondary btn-md" :disabled="page==1">Previous</button>
            <button @click="next" class="btn btn-secondary btn-md" :disabled="page>=totalPages">Next</button>
        </div>

    </div>
</template>
