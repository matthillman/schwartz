<template>
    <div class="search-wrapper">
        <div class="row add-row">
            <input class="form-control" type="text" value="" placeholder="Search" name="query" v-model="search">
            <div v-if="helpNote.length" class="small-note">{{ helpNote }}</div>
        </div>
        <div class="search-results-wrapper" v-if="results || searching">
            <div :class="resultsClass" v-for="item in results.data" :key="item.id">
                <slot v-bind:item="item">
                    {{item.name}}
                </slot>
            </div>
            <div v-if="!results.data && search.length">
                <em>No results found</em>
            </div>

            <div class="pagination-wrapper flex-center" v-if="results.current_page != 1 || results.next_page_url">
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

                        <li v-for="page in pageWindow()" :key="page" class="page-item" :class="{'active': page == results.current_page, 'disabled': !Number.isInteger(page)}">
                            <a v-if="Number.isInteger(page)" class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                            <span v-else class="page-link">…</span>
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

            <div v-if="searching" class="element-overlay">
                <loading-indicator></loading-indicator>
            </div>
        </div>


    </div>
</template>

<script>
export default {
    props: {
        url: String,
        helpNote: {
            type: String,
            default: '',
        },
        resultsClass: {
            type: Array,
            default: ['column'],
        }
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
        }, 250),

        pageWindow() {
            const windowSize = 2 /* on each side */ * 2;

            if (!this.results || !this.results.last_page) {
                return [];
            }

            if (this.results.last_page < windowSize + 6) {
                return [...Array.from({length: this.results.last_page}, (x, i) => i + 1)];
            }

            if (this.results.current_page <= windowSize) {
                return [
                    ...Array.from({length: windowSize + 2}, (x, i) => i + 1),
                    '…',
                    this.results.last_page - 1,
                    this.results.last_page,
                ]
            } else if (this.results.current_page > (this.results.last_page - windowSize)) {
                return [
                    1,
                    2,
                    '…',
                    ...Array.from({length: windowSize + 2}, (x, i) => i + this.results.last_page - windowSize - 1),
                ]
            }

            return [
                1,
                2,
                'spacer1',
                ...Array.from({length: windowSize + 1}, (x, i) => i + this.results.current_page - 2),
                'spacer2',
                this.results.last_page - 1,
                this.results.last_page,
            ]
        }
    }
}
</script>

<style lang="scss" scoped>
.search-results-wrapper {
    position: relative;
}
.pagination-wrapper {
    margin-top: 16px;
}
</style>