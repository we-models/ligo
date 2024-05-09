<template>
    <div>
        <language-selector-component :lngs="lngs" :lng="lng"
            :title="`${$t('Assign')} <small>${Cts.formatClassName(rows)}</small> ${$t('to')} <small>${Cts.formatClassName(columns)}</small>`">
        </language-selector-component>

        <div style="padding:15px">
            <div class="table table-responsive">
                <table class='table table-bordered '>
                    <thead>
                        <tr>
                            <th>
                                <progress-component :progress="this.progress" />
                            </th>
                            <th :colspan="pagination.data !== undefined ? pagination.headers.length : 1" class="center">
                                <h5><strong>{{ Cts.formatClassName(rows) }}</strong></h5>
                            </th>
                        </tr>
                        <tr>
                            <th class="table-column-header">

                                <h6><strong>{{ Cts.formatClassName(columns) }}</strong></h6>

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <button class="btn btn-primary" v-on:click="fillGrid('')" type="button">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </span>
                                    <input type="search" class="form-control search_form" :placeholder="$t('Search')"
                                        v-model="search" v-on:keyup="fillGrid('')">
                                </div>
                            </th>
                            <th v-for="header in pagination.headers"> {{ header }}</th>
                        </tr>
                    </thead>
                    <tbody v-if="pagination.data !== undefined">
                        <tr v-for="row in pagination.data">
                            <th class="vertical-fixed">
                                <div v-if="row.identifier !== undefined">{{row.identifier}}</div>
                                <ul>
                                    <li>{{ row.name }}</li>
                                </ul>
                            </th>
                            <td v-for="relation in row.relations"
                                :class="'cell ' + (relation.relation ? 'cell-selected ' : '')"
                                v-on:click="saveRelation(row, relation)">

                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>
                                <div class="mb-3 row">
                                    <label class="col-6 form-label" style="text-align:right">{{ $t('Items by page')}}</label>
                                    <div class="col-6">
                                        <input type="number" step="1" class="form-control" min="1" max="1000"
                                            v-model="paginate" v-on:keyup="fillGrid('')" v-on:change="fillGrid('')">
                                    </div>
                                </div>
                            </td>
                            <td :colspan="pagination.headers.length" v-if="pagination.data !== undefined">
                                <nav aria-label="Page navigation" class="nav justify-content-end"
                                    v-if="pagination.last_page > 1">
                                    <ul class="pagination">
                                        <li v-for="(link, key) in pagination.links"
                                            v-bind:class="['page-item', link.active ? 'active' : '']">
                                            <button class="page-link" v-on:click="fillGrid('', link.label, key)"
                                                v-html="$t(link.label)"></button>
                                        </li>
                                    </ul>
                                </nav>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

import cts from './Constants';

export default {
    name: "AssignComponent.vue",
    props: ['columns', 'rows', 'the_key', 'url', 'url_to_save', 'csrf', 'lngs', 'lng', 'unique', 'general'],
    data() {
        return {
            Cts: cts,
            pagination: [],
            search: '',
            paginate: 10,
            progress: 0,
            link_list: this.url,
            page: 1
        }
    },
    created() {
        this.link_list = `${this.link_list}/?x=${this.rows}&y=${this.columns}&key=${this.the_key}&general=${this.general}`
        this.fillGrid("");
    },
    methods: {
        fillGrid: function (uri, pg = this.page, tk = 0) {
            if (tk === 0 && pg > 1) pg = pg - 1;

            if (this.pagination.length > 0) {
                let pgnt = this.pagination.links.length - 1;
                if (tk === pgnt && pg < this.pagination.last_page) pg = pg + 1;
            }
            this.page = pg;
            this.pagination = [];
            fetch(this.encodeURL(uri)).then(response => response.json()).then((data) => {
                this.pagination = data;
            }).catch((error)=>{
                console.log(error)
            });
        },
        encodeURL: function (uri) {
            if (uri === "") uri = this.link_list;
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
            if (this.page > 1) uri = this.Cts.fillUrlParameters(uri, 'page', this.page)
            return uri;
        },
        saveRelation: function (row, relation) {
            if (this.progress !== 0) return;
            this.pagination.data = this.changeRelation(row, relation, false);
            let jsonForm = { x: relation.id, y: row.id, type: relation.relation, key: this.the_key};

            if(this.unique){
                this.changeRelation(row, relation, true);
            }

            jQuery.ajax(this.url_to_save, {
                method: 'POST',
                data: jsonForm,
                headers: { 'X-CSRF-TOKEN': this.csrf },
                xhr: () => {
                    let xhr = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        this.progress = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    (new Audio(location.origin + '/sounds/success.mp3')).play();
                },
                error: (_error) => {
                    (new Audio(location.origin + '/sounds/error.ogg')).play();
                    this.pagination.data = this.changeRelation(row, relation, false);
                },
                complete: () => {
                    this.progress = 0;
                }
            });
        },
        changeRelation: function (row, relation, clean) {
            return this.pagination.data.map((r) => {
                if (r.id === row.id) {
                    if(clean){
                        r.relations = r.relations.map((c) => {
                            c.relation = false;
                            return c;
                        });
                    }
                    r.relations = r.relations.map((c) => {
                        if (c.id === relation.id) c.relation = !c.relation;
                        return c;
                    });
                }
                return r;
            });
        }
    }
}
</script>

<style scoped>
    thead th{
        vertical-align: middle;
    }
</style>
