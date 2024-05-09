<template>
    <div>
        <language-selector-component
            :lngs="lngs"
            :lng="lng"
            :title="title" />

        <div class="padding">
            <div class="panel-flex">
                <div style="flex: 4;">
                    <div style="display: flex; gap:10px; align-items: center;">
                        <div style="flex:4">
                            <div class="form-group">
                                <label class="form-label"> {{$t('Select object type')}} </label>
                                <object-selector-component
                                    :ent = "this.entity"
                                    llave="object_type"
                                    :value = "objectType.attributes"
                                    :csrf = "csrf"
                                    :required = "true"
                                    :readonly = "false"
                                    :multiple="false"
                                    :paginate = "12"
                                    type = "object"
                                    :depends = "[]"
                                    @onSetSelectedItems = "changeObjectType"
                                />
                            </div>
                        </div>
                        <div style="flex:4" v-if="this.entity.object_type != null">
                            <div class="form-group">
                                <label class="form-label"> {{$t('Search')}} </label>
                                <input type="search" class="form-control" :placeholder="$t('Search')" v-model="search">
                            </div>
                        </div>
                        <div style="flex:1" v-if="this.entity.object_type != null">
                            <label for=""></label>
                            <button v-on:click="applyFilters('')" class="btn btn-primary" type="button">{{$t('Apply')}}</button>
                        </div>
                    </div>
                    <div>
                        <div class="row" style="margin-bottom: 1em">
                            <div class="col-lg-2">
                                <label for=""> {{$t('By Visible')}} </label>

                                <field-component
                                    :ent="'1'"
                                    name = "enable"
                                    :type = "{id: 13, name:'Boolean'}"
                                />
                            </div>
                            <div class="col-lg-6">
                                <label for=""> {{$t('By owner')}} </label>
                                <object-selector-component
                                    :ent = "this.entity"
                                    llave="owner"
                                    :value = "owner.attributes"
                                    :csrf = "csrf"
                                    :required = "true"
                                    :readonly = "false"
                                    :multiple="true"
                                    :paginate = "12"
                                    type = "object"
                                    :depends = "[]"
                                    @onSetSelectedItems = "changeOwner"
                                />
                            </div>
                        </div>

                        <object-type-fields-filter-component
                            v-if="this.current_object_type != null"
                            :object_type="this.current_object_type"
                            :filter_link="this.filter_link" ref="ObjectFields"
                            :readonly = "this.progress"
                            :csrf = "this.csrf"
                            :prefix = "''"
                        />
                    </div>
                </div>
                <div style="flex: 7">
                    <div class="table-responsive" v-if="pagination != null">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{$t('Results')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in pagination.data">
                                    <td>
                                        <p>
                                            <strong>ID:</strong> {{item.id}}
                                        </p>
                                        <p>
                                            <strong>Name:</strong> {{item.name}}
                                        </p>
                                        <div class="cf-object" style="max-height:500px; overflow-y:scroll">
                                            <div style="margin:5px 0;" v-html="item.description"></div>
                                        </div>

                                        <filter-result-component :item="item" :showName = "false" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="pagination != null">
                        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1">
                            <ul class="pagination">
                                <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                                    <button class="page-link" v-if="link.url != null" v-on:click="applyFilters(link.url)"
                                            v-html="$t(link.label)"></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import cts from '../Constants';
import { nextTick } from 'vue'
import ObjectFieldComponent from "../ObjectFieldComponent";
import { useFilterStore } from '../../stores/FilterStore.js';
export default {
    components: {ObjectFieldComponent},
    props : ['object_type', 'lngs', 'lng', 'title', 'csrf', 'filter_link', 'filtered_link', 'owner'],
    name: "ReportComponent",
    data(){
        return {
            Cts: cts,
            search : "",
            entity : {},
            objectType : JSON.parse(this.object_type),
            owner : JSON.parse(this.owner),
            progress : false,
            paginate : 10,
            page: 1,
            sort: 'id',
            sort_direction: 'asc',
            url_parameter : "",
            apply_url : this.filtered_link,
            pagination: null,
            current_object_type : null,

            enable : true,
            owners : [],

            filterStore : null
        }
    },
    mounted() {
        this.filterStore = useFilterStore()
    },
    methods : {
        changeObjectType : function(k, s){
            this.filterStore.clean();
            this.current_object_type = s;
            nextTick(() => {
                if(this.current_object_type != null) {
                    this.applyFilters();
                    this.$refs.ObjectFields.init();
                }
            });

            if(s == null){
                this.pagination = null;
            }
        },
        applyFilters : function(uri=""){
            this.apply_url = this.encodeURL(uri);
            this.progress = true;
            this.pagination = null;
            fetch(this.apply_url).then(response => response.json())
                .then((data) => {
                    this.pagination = data;
                }).catch(error=>{
                    console.log(error);
                }).finally(()=>{
                    this.progress = false;
                });
        },
        changeOwner : function(k, s){
          this.owners = s.map(us=>us.id);
        },
        encodeURL: function (uri, pdf = null) {
            let isNotFirst = false;
            if (uri === "") {
                uri = this.filtered_link;
                isNotFirst = true;
            }
            if(!isNotFirst && this.url_parameter.length > 0){
                let prefix = this.url.includes('?') ? "&" : "?";
                uri +=  `${prefix}${this.url_parameter}`;
            }
            uri = this.Cts.fillUrlParameters(uri, 'object_type', this.current_object_type.id);
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            if (this.paginate !== 10) uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
            if (pdf != null) uri = this.Cts.fillUrlParameters(uri, 'pdf', 1);
            if(this.filterStore.filters.length > 0){
                let allFilters = {
                    "type": "AND",
                    "expressions":this.filterStore.filters
                }
                uri = this.Cts.fillUrlParameters(uri, 'condition', JSON.stringify(allFilters));
            }
            if (this.sort.length > 0 && this.sort_direction.length > 0) {
                uri = this.Cts.fillUrlParameters(uri, 'sort', this.sort);
                uri = this.Cts.fillUrlParameters(uri, 'direction', this.sort_direction);
            }

            uri = this.Cts.fillUrlParameters(uri, 'enabled', this.enable);
            if(this.owners.length > 0){
                uri = this.Cts.fillUrlParameters(uri, 'owners', this.owners);
            }
            console.log("Encoded uri: ",uri);
            return uri;
        }
    }
}
</script>

<style scoped>
    .padding{
        padding: 1em;
    }
    .panel-flex{
        display: flex;
        gap:25px;
        flex-direction : row;
    }
    .cf-object{
        max-height: 250px;
        overflow-y: scroll;
        overflow-x: hidden;
    }
    .img-field{
        height: 50px;
        width: 50px;
    }
    @media screen and (max-width: 1024px) {
        .panel-flex{
            flex-direction : column;
        }
    }
</style>
