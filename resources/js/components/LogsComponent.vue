<template>
    <div>
        <div class="row">
            <div class="col-lg-8">

            </div>
            <div class="col-lg-4">
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <button class="btn btn-primary" v-on:click="fillLogList('')" type="button">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </span>
                    <span class="input-group-text">
                        <a class="btn btn-primary" :href="encodeURL(current_link, true)" target="_blank">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                    </span>
                    <input type="search" class="form-control search_form" :placeholder="$t('Search')" v-model="search"
                        v-on:keyup="fillLogList('')">
                </div>
            </div>

            <div class="col-lg-9 col-md-7"></div>
            <div class="col-lg-3 col-md-5">
                <div class="mb-3 row">
                    <label class="col-6 form-label" style="text-align:right">{{ $t('Items by page') }}</label>
                    <div class="col-6">
                        <input type="number" step="1" class="form-control" min="1" max="1000" v-model="paginate"
                            v-on:keyup="fillLogList('')" v-on:change="fillLogList('')">
                    </div>
                </div>
            </div>

        </div>


        <div class="table-responsive table-mobile">
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th>{{ $t('ID') }}</th>
                        <th>{{ $t('Causer') }}</th>
                        <th>{{ $t('Description') }}</th>
                        <th>{{ $t('Date time') }}</th>
                        <th style="min-width: 400px">{{ $t('Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="progress">
                        <td colspan="5">
                            <div class="interface_loader"></div>
                        </td>
                    </tr>

                    <tr v-for="log in pagination.data" v-if="!progress">
                        <td>{{ log.id }}</td>
                        <td>
                            <p v-if="log.causer !== null">
                                {{ log.causer.name }}
                                <a :href="'mailto:' + log.causer.email">{{ log.causer.email }}</a>
                            </p>
                        </td>
                        <td>{{ log.description }}</td>
                        <td>
                            {{ this.Cts.reformatDateTime(log.created_at) }}
                        </td>
                        <td>
                            <a class="" data-bs-toggle="collapse" :href="'#collapse_' + log.id" role="button"
                                aria-expanded="false" :aria-controls="'collapse_' + log.id">
                                {{ $t('Toggle details') }}
                            </a>
                            <div class="collapse" :id="'collapse_' + log.id">
                                <hr>
                                <div class="card card-body log_objects">
                                    <div v-for="key in Object.keys(log.properties.attributes)"
                                        class="row log_row_detail" v-if="log.properties.attributes !== undefined">
                                        <div class="col-3">
                                            <strong class="log_title">{{ Cts.reformatTitle(key) }}</strong>
                                        </div>
                                        <div class="col-9" v-html="Cts.formatLog(key, log.properties.attributes[key])">
                                        </div>
                                    </div>

                                    <div v-for="key in Object.keys(log.properties.old)" class="row log_row_detail"
                                        v-if="log.properties.old !== undefined">
                                        <div class="col-3">
                                            <strong class="log_title">{{ Cts.reformatTitle(key) }}</strong>
                                        </div>
                                        <div class="col-9" v-html="Cts.formatLog(key, log.properties.old[key])">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1">
            <ul class="pagination">
                <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                    <button class="page-link" v-if="link.url != null" v-on:click="fillLogList(link.url)"
                        v-html="$t(link.label)"></button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>

import cts from './Constants';

export default {
    name: "LogsComponent",
    props: ['url'],
    data() {
        return {
            Cts: cts,
            pagination: [],
            search: '',
            progress: false,
            paginate: 10,
            current_link: this.url
        }
    },
    created() {
        this.fillLogList('');
    },
    methods: {
        fillLogList: function (uri) {
            this.pagination = [];
            this.progress = true;
            fetch(this.encodeURL(uri)).then(response => response.json()).then((data) => {
                this.pagination = data;
            }).finally(() => {
                this.progress = false;
            });
        },
        encodeURL: function (uri, pdf = null) {
            if (uri === "") uri = this.url;
            this.current_link = uri;
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            if (this.paginate !== 10) uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
            if (pdf != null) uri = this.Cts.fillUrlParameters(uri, 'pdf', 1);
            return uri;
        }
    }
}
</script>

<style scoped>

</style>
