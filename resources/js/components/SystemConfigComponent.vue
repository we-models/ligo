<template>
    <div>
        <language-selector-component :lngs="lngs" :lng="lng" :title="$tChoice(title.toUpperCase(), 2)">
        </language-selector-component>

        <div class="vue-layout">
            <div class="d-flex align-items-start" v-if="selected != null">
                <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical"
                    v-if="!showTitle">
                    <button v-for="type_item in list_types"
                        :class="'nav-link ' + (type_item['id'] === selected.id ? 'active' : '')"
                        :id="'v-pills-' + type_item['id'] + '-tab'" data-bs-toggle="pill"
                        :data-bs-target="'#v-pills-' + type_item['id']" type="button" role="tab"
                        :aria-controls="'v-pills-' + type_item['id']" :aria-selected="type_item['id'] === selected.id"
                        v-on:click="setTypeSelected(type_item)">
                        {{ type_item['name'] }}
                    </button>
                </div>
                <div class="tab-content" id="v-pills-tabContent">
                    <div style="padding: 10px" v-if="showTitle && selected != null">
                        <div class="dropdown" style="text-align: center">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                {{ selected.name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li v-for="type_item in list_types">
                                    <a class="dropdown-item" href="#"
                                        v-on:click="setTypeSelected(type_item)">{{ type_item.name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div style="padding:3em" v-if="loading">
                        <div class="interface_loader"></div>
                    </div>
                    <div style="padding:2em; text-align: center" v-if="!loading && pagination.total === 0">
                        <h1>{{ $t('No data available') }}</h1>
                    </div>
                    <div v-for="type_item in list_types"
                        :class="'tab-pane fade ' + (type_item['id'] === selected.id ? 'show active' : '')"
                        :id="'v-pills-' + type_item['id']" role="tabpanel"
                        :aria-labelledby="'v-pills-' + type_item['id'] + '-tab'" tabindex="0">
                        <div v-if="!loading">
                            <div class="panel-scrolled" style="padding:0.5em;" v-if="type_item['id'] === selected.id">
                                <div class="flex-container" v-if="pagination !== []" v-for="item in pagination.data">
                                    <div style="flex-grow: 1">
                                        <div class="flex-title" v-if="first.id === item.id || showTitle">
                                            {{ $t('Name') }}
                                        </div>
                                        <div class="flex-content">
                                            {{ item.name }}
                                        </div>
                                    </div>
                                    <div style="flex-grow: 1">
                                        <div class="flex-title" v-if="first.id === item.id || showTitle">
                                            {{ $t('Description') }}
                                        </div>
                                        <div class="flex-content" v-html="item.description"></div>
                                    </div>
                                    <div style="flex-grow: 3; max-width: unset">

                                        <div class="flex-title" v-if="first.id === item.id || showTitle">
                                            {{ $t('Value') }}
                                        </div>
                                        <div class="flex-content">

                                            <div
                                                v-if="item.progress > 0"
                                                class="progress"
                                                role="progressbar"
                                                aria-label="Animated striped example"
                                                :aria-valuenow="item.progress"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            >
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" :style="'width: ' + item.progress + '%'"></div>
                                            </div>

                                            <input v-if="Cts.input_types.includes(item.type.name)"
                                                v-bind="getTypeAttributes(item)" class="form-control" v-model="item.configuration.value">

                                            <div class="form-check form-switch" v-if="item.type.name === 'Boolean'">
                                                <input type="checkbox" class="form-check-input" v-model="item.configuration.value" :checked="item.configuration.value === '1' || item.configuration.value === 'true' ">
                                            </div>

                                            <textarea class="form-control" :placeholder="item.default ?? ''"
                                                v-if="item.type.name === 'Text'" v-model="item.configuration.value" />

                                            <text-area-component v-if="item.type.name === 'Html'"
                                                :ent="item.configuration != null ? item.configuration.value : ''"
                                                :theKey="'config-' + item.id" :value="''" :toolbar="Cts.quill_toolbar"
                                                :placeholder="item.default ?? ''" />


                                            <div class="btn-config">
                                                <button type="button" class="btn btn-dark" v-on:click="saveConfiguration(item)">{{$t('Save')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1">
                        <ul class="pagination">
                            <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                                <button class="page-link" v-if="link.url != null" v-on:click="getValuesFromLink(link.url)"
                                        v-html="$t(link.label)"></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import cts from './Constants';

export default {
    props: ['csrf', 'title', 'all', 'lngs', 'lng', 'create', 'index', 'types', 'store'],
    name: "SystemConfigComponent",
    created() {
    },
    data() {
        return {
            Cts: cts,
            list_types: JSON.parse(this.types),
            lang: this.lng,
            langs: this.lngs,
            selected: null,
            pagination: [],
            first: null,
            showTitle: false,
            loading: false
        }
    },
    mounted: function () {
        this.setTypeSelected(this.list_types[0]);
        this.getDimensions();
        window.addEventListener('resize', this.getDimensions);
    },
    unmounted: function () {
        window.removeEventListener('resize', this.getDimensions);
    },
    methods: {
        setTypeSelected: function (type) {
            this.loading = true;
            this.selected = type;
            let uri = this.all + '?type=' + this.selected.id
            this.getValuesFromLink(uri);
        },
        getValuesFromLink: function(uri) {
            this.loading = true;
            uri = uri + "&type=" + this.selected.id;
            fetch(uri).then(response => response.json()).then((data) => {
                this.pagination = data;
                this.first = data.data[0] ?? null;
                for(let i=0; i < this.pagination.data.length; i++){
                    if(this.pagination.data[i].configuration == null) {
                        this.pagination.data[i].configuration = {"value": "", "exists" : false};
                    }else{
                        this.pagination.data[i].configuration.exists = true;
                    }
                    this.pagination.data[i].progress =0;
                }
            }).finally(() => {
                this.loading = false;
            });
        },
        getDimensions: function () {
            this.showTitle = document.documentElement.clientWidth <= 768;
        },
        getTypeAttributes: function (item) {
            let vTypes = this.Cts.value_types;
            let response = {
                placeholder: item.default ?? '',
                value: item.configuration != null ? item.configuration.value : '',
            }
            response.type = vTypes[item.type.name].type;
            if (vTypes[item.type.name].step !== undefined) response.step = vTypes[item.type.name].step;
            return response;
        },
        saveConfiguration : function(item){
            item.progress = 0;
            jQuery.ajax(this.store, {
                method: 'POST',
                data: item,
                headers: { 'X-CSRF-TOKEN': this.csrf },
                xhr: () => {
                    let xhr = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        item.progress = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    item.configuration.exists = true;
                    item.configuration.id =  _response.configuration;
                },
                error: (_error) => {

                },
                complete: () => {
                    item.progress = 0;
                }
            });
        }
    }
}
</script>
