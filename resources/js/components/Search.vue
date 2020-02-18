<template>
    <div class="search-wrapper">
        <div class="row add-row">
            <input type="text" value="" placeholder="Search" name="query" v-model="search">
            <div class="small-note">Searches guild name and guild ID</div>
        </div>
        <div v-if="searching">
            <loading-indicator></loading-indicator>
        </div>
        <div v-else>
            <div class="column" v-for="item in results.data" :key="item.id">
                <slot v-bind:item="item">
                    {{item.name}}
                </slot>
            </div>
            <div v-if="!results.data && search.length">
                <em>No results found</em>
            </div>
        </div>

        <div class="flex-center" v-if="results.current_page != 1 || results.next_page_url">
            <nav>
                <ul class="pagination">
                    <li v-if="results.current_page <= 1" class="page-item disabled">
                        <span class="page-link" aria-hidden="true">&laquo;</span>
                    </li>
                    <li v-else class="page-item" aria-disabled="true" aria-label="First">
                        <a class="page-link" href="#" @click.prevent="changePage(1)" rel="first" aria-label="First">&laquo;</a>
                    </li>
                    <li v-if="results.current_page <= 1" class="page-item disabled" aria-disabled="true" aria-label="Previous">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                    </li>
                    <li v-else class="page-item">
                        <a class="page-link" href="#" @click.prevent="changePage(results.current_page - 1)" rel="prev" aria-label="Previous">&lsaquo;</a>
                    </li>

                    <li v-for="page in results.last_page" :key="page" class="page-item" :class="{'active': page == results.current_page}">
                        <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>


                    <li v-if="results.current_page < results.last_page" class="page-item">
                        <a class="page-link" href="#" @click.prevent="changePage(results.current_page + 1)" rel="next" aria-label="Next">&rsaquo;</a>
                    </li>
                    <li v-else class="page-item disabled" aria-disabled="true" aria-label="Next">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                    </li>
                    <li v-if="results.current_page < results.last_page" class="page-item">
                        <a class="page-link" href="#" @click.prevent="changePage(results.last_page)" rel="last" aria-label="Last">&raquo;</a>
                    </li>
                    <li v-else class="page-item disabled" aria-disabled="true" aria-label="Last">
                        <span class="page-link" aria-hidden="true">&raquo;</span>
                    </li>
                </ul>
            </nav>
        </div>

    </div>
</template>

<script>
export default {
    props: {
        url: String,

    },
    data() {
        return {
            search: '',
            results: { data: [] },
            searching: false,
        };
    },

    watch: {
        search(newVal) {
            this.maybeDoSearch(newVal);
        }
    },

    mounted() {
        this.maybeDoSearch('');
    },

    methods: {
        changePage: function(page) {
            this.maybeDoSearch(this.search, page);
        },
        maybeDoSearch: _.debounce(async function(search, page = null) {
            this.searching = true;
            if (!page) {
                if (response) {
                    page = response.current_page;
                } else {
                    page = 1;
                }
            }
            const response = await axios.get(`${this.url}?page=${page}&search=${search}`);

            this.searching = false;
            this.results = response.data;
        }, 250)
    }
}
</script>