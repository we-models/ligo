<template>
    <div>
        <!--        <div style="text-align: right">{{this.list_api}}</div>-->
        <!--        <br>-->

        <alert-component :listener="alert_listener" />

        <div class="row">
            <div class="col-lg-4 list_selected">
                <div style="padding: 0px 0px 10px 0px">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-start"
                            v-for="item in selected">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">{{ $t('ID') }}: {{ item.id }}, {{ $t('name') }}: {{ item.name }}</div>
                            </div>
                            <a href="javascript:void(0)" v-on:click="removeSelected(item)">
                                <span class="text-danger"><i class="fa-solid fa-xmark"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4"></div>
            <div class="col-lg-4">

                <div class="input-group mb-3">
                    <span class="input-group-text btn_link_span">
                        <i v-on:click="fillList('')" class="fa-solid fa-rotate"></i>
                    </span>
                    <span class="input-group-text btn_link_span">
                        <a :href="encodeURL(current_link, true)" target="_blank">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                    </span>
                    <input type="search" class="form-control search_form" :placeholder="$t('Search')" v-model="search"
                        v-on:keyup="fillList('')">
                </div>


            </div>
            <div class="col-lg-9 col-md-7"></div>
            <div class="col-lg-3 col-md-5">
                <div class="mb-3 row">
                    <label class="col-6 form-label" style="text-align:right">{{ $t('Items by page') }}</label>
                    <div class="col-6">
                        <input type="number" step="1" class="form-control" min="1" max="1000" v-model="paginate"
                            v-on:keyup="fillList('')" v-on:change="fillList('')">
                    </div>
                </div>
            </div>
        </div>

        <progress-component :progress="this.progress_value" />

        <div class="table-responsive table-mobile">
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th class="btn_icons" v-if="this.multiple == null">
                            {{ $t('Options') }}
                        </th>
                        <th class="" v-if="this.multiple != null">
                            {{ $t('Select all') }}
                            <input id="checkAll" style="position: relative;top: 2px;left: 5px;" readonly type="checkbox" :name="name_choose" v-on:change="setSelectionAll()">
                        </th>
                        <th v-for="(field, key) in entity">
                            <div style="display: ruby">
                                <strong
                                    v-if="all_fields[key] !== undefined">{{ all_fields[key].properties.label }}</strong>
                                <strong v-if="all_fields[key] === undefined">{{ $t(key) }}</strong>
                                <div class="sort-buttons" v-if="all_fields[key] !== undefined">
                                    <a href="javascript:void(0)" v-on:click="refillDirection(key, 'asc')">
                                        <span>
                                            <i
                                                :class="(all_fields[key].direction !== 'asc' ? '' : 'bt-blue') + ' fa-solid fa-caret-up'" />

                                        </span>
                                    </a>
                                    <a href="javascript:void(0)" v-on:click="refillDirection(key, 'desc')">
                                        <span>
                                            <i
                                                :class="(all_fields[key].direction !== 'desc' ? '' : 'bt-blue') + ' fa-solid fa-caret-down'" />
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in pagination.data" v-if="!progress"
                        v-on:dblclick="selectAndSend('inp_' + item['id'])"
                        v-on:click="setSelectionByRow('inp_' + item['id'])"
                        :class="name_choose != null ? 'possible-selection' : ''">
                        <td class="btn_icons" v-if="this.multiple == null">
                            <a href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'SHOW', this.index)"
                                class="btn_view" v-if="permissions.includes('.show')">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'PUT', this.index)"
                                class="btn_edit" v-if="permissions.includes('.edit')">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" v-on:click="deleteItem(item)" class="btn_delete"
                                v-if="permissions.includes('.destroy')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <a href="javascript:void(0)" v-on:click="commentItem(item)" class="btn_view" v-if="object_class.includes('TheObject')">
                                <i class="fa fa-comment"></i>
                            </a>
                            <a href="javascript:void(0)" v-on:click="duplicate(item)" class="btn_view" v-if="object_class.includes('TheObject') && item.object_type.type === 'post'">
                                <i class="fa-solid fa-copy"></i>
                            </a>
                        </td>
                        <td v-if="this.multiple && name_choose != null">
                            <input readonly type="checkbox" :id="'inp_' + item['id']" :name="name_choose"
                                :value="item['id']" v-on:change="setSelection($event, item)"
                                :class="'checkbox_'+name_choose" :data-name-item="item['name']" :checked="checkedVerify(item)">
                        </td>
                        <td v-if="!this.multiple && name_choose != null">
                            <input readonly type="radio" :id="'inp_' + item['id']" :name="name_choose"
                                :value="item['id']" v-on:click="setSelection($event, item)">
                        </td>
                        <td v-for="(field, key) in entity">
                            <div v-if="all_fields[key] === undefined" >
                                {{ formatJson(item[key]) }}
                            </div>
                            <div v-html="Cts.formatCell(all_fields[key], item[key])" class="item-data"
                                v-if="all_fields[key] !== undefined && (all_fields[key].attributes.type !== 'object' && all_fields[key].attributes.type !== 'image')" />

                            <div v-if="all_fields[key] !== undefined &&  all_fields[key].attributes.type === 'image' && item[key] !== null" >
                                <div style="display: flex; gap: 15px;max-width: 400px">
                                    <div v-for="line in item[key]" :style="`background-image: url('${line.small}')`" class="img-card">

                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="all_fields[key] !== undefined && all_fields[key].attributes.type === 'object' && item[key] !== null">
                                <div v-if="!Array.isArray(item[key])">
                                    <a href="javascript:void(0)"
                                        v-on:click="methodsLine(item[key].id, 'SHOW', all_fields[key].attributes.data.index)">
                                        {{ item[key].id }}: {{ item[key].name }}
                                    </a>
                                </div>

                                <div v-if="Array.isArray(item[key])" v-for="line in item[key]">
                                    <a href="javascript:void(0)"
                                        v-on:click="methodsLine(line.id, 'SHOW', all_fields[key].attributes.data.index)">
                                        {{ line.id }}: {{ line.name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="progress">
                        <td :colspan="Object.keys(entity).length + 1">
                            <div class="interface_loader"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1">
            <ul class="pagination">
                <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                    <button class="page-link" v-if="link.url != null" v-on:click="fillList(link.url)"
                        v-html="$t(link.label)"></button>
                </li>
            </ul>
        </nav>
        <nav class="nav justify-content-start" v-if="this.multiple != null">
            <ul class="pagination">
                <button id="markAsSelectedButton" class="btn btn-primary"
                    :disabled="this.required && this.selected.length === 0" type="button"
                    @click="$emit('onSelected', selected)">
                    {{ $t('Mark as selected') }}
                </button>
            </ul>
        </nav>
        <modal-form-component v-if="this.multiple == null" v-once  />
        <modal-comment-component
            :csrf="this.csrf"
            v-if="this.to_comment !== null"
            :object="this.to_comment"
            :comments_url="comments_url"
            :rating_types_url = "this.rating_types_url"
        ></modal-comment-component>
    </div>
</template>

<script>
import cts from './Constants';
import { trans } from 'laravel-vue-i18n';
import { nextTick } from 'vue'

export default {
    props: [
        'object', 'fields', 'url', 'permissions', 'index', 'csrf', 'multiple', 'name_choose',
        'onSelected', 'itemsSelected', 'req', 'object_class', 'comments_url', 'rating_types_url'
    ],
    name: "ListComponent",
    data() {
        return {
            Cts: cts,
            all_fields: this.formatedList(this.fields),
            entity: this.object,
            search: "",
            pagination: [],
            progress: false,
            sort: 'id',
            sort_direction: 'asc',
            list_api: '',
            paginate: 10,
            current_link: this.url,
            selected: this.getItemsSelected(),
            required: this.req === undefined ? false : this.req,
            url_parameter : "",
            progress_value : 0,
            to_comment : null,
            alert_listener : new Date().getTime(),
            selectedAll : false,
            items_list: []
        }
    },
    created() {
        this.getParams(this.url);
        this.fillList("");
    },
    methods: {
        commentItem : function(item){
            this.to_comment = item;
            nextTick(() => {
                this.emitter.emit("fillComments", { "item" : item.id });
            })
        },
        duplicate(item){
            let uri = `${this.index}/duplicate`
            jQuery.ajax(uri, {
                method: 'POST',
                data: {object : item.id},
                headers: { 'X-CSRF-TOKEN':  this.csrf },
                xhr: ()=> {
                    let xhr = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        this.progress = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    this.emitter.emit("alert_" + this.alert_listener, {'success' : true, 'txt': trans('The data was cloned successfully')});
                    (new Audio(location.origin + '/sounds/success.mp3')).play();
                },
                error: (error)=> {
                    console.log(error);
                    (new Audio(location.origin + '/sounds/error.ogg')).play();
                    this.emitter.emit("alert_" + this.alert_listener, {'success' : false, 'txt': error.responseText});
                },
                complete: ()=> {
                    this.progress = 0;
                    this.fillList("");
                }
            });
        },
        getParams : function (url) {
            url = new URL(url);
            const urlParams = new URLSearchParams(url.search);
            var parameters = "";
            for (let paramName of urlParams.keys()) {
                let prefix = this.url_parameter.includes('?') ? "&" : "";
                parameters += `${prefix}${paramName}=${urlParams.get(paramName)}`;
            }
            this.url_parameter = parameters;
        },
        getItemsSelected: function () {
            if (this.itemsSelected === undefined) return [];
            if (this.itemsSelected === null) return [];
            if (Array.isArray(this.itemsSelected)) return this.itemsSelected;
            if (!Array.isArray(this.itemsSelected)) return [this.itemsSelected];
            return [];
        },
        formatedList: function (fs) {
            let values = fs;
            Object.keys(values).forEach(c => { values[c].direction = "" });
            return values;
        },
        fillList: function (uri) {
            this.pagination = [];
            this.progress = true;
            let the_uri = this.encodeURL(uri);
            fetch(the_uri).then(response => response.json()).then((data) => {
                this.pagination = data;
            }).finally(() => {
                this.progress = false;
                nextTick(() => {
                    this.selected.forEach((sel) => {
                        let input = this.$el.querySelector(`input[name="${this.name_choose}"][value="${sel.id}"]`);
                        if (input == null) return;
                        input.setAttribute('checked', true);
                        input.checked = true;
                    });
                });
            });
        },
        methodsLine: function (id, method, link) {
            this.emitter.emit("fillModalCRUD", { 'index': link, 'id': id, 'method': method });
        },
        deleteItem: function (item) {
            Swal.fire({
                title: trans('Are you sure?'),
                text: trans("You won't be able to revert this!"),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#dc3545',
                confirmButtonText: trans('Yes, delete it!')
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery.ajax(`${this.index}/${item['id']}`, {
                        method: 'DELETE',
                        data: { "_token": this.csrf },
                        xhr: ()=> {
                            let xhr = new XMLHttpRequest();
                            xhr.upload.onprogress = (e) => {
                                this.progress_value = Math.round((e.loaded / e.total) * 98);
                            };
                            return xhr;
                        },
                        success: (response) => {
                            this.fillList('');
                            Swal.close();
                        },
                        error: (error) => {
                            this.fillList('');
                            Swal.fire({
                                icon: 'error',
                                title: trans('Oops...'),
                                text: error.responseText,
                            });
                        },
                        complete: ()=> {
                            this.progress_value = 0;
                        }
                    });
                }
            })
        },
        refillDirection: function (key, direction) {
            let clean = false;
            if (this.all_fields[key].direction === direction) clean = true;
            Object.keys(this.all_fields).forEach(k => { this.all_fields[k].direction = "" });
            if (clean) {
                this.sort = this.sort_direction = '';
            } else {
                this.sort = key;
                this.sort_direction = this.all_fields[key].direction = direction;
            }
            this.fillList('');
        },

        encodeURL: function (uri, pdf = null) {
            let isNotFirst = false;
            if (uri === "") {
                uri = this.url;
                isNotFirst = true;
            }
            if(!isNotFirst && this.url_parameter.length > 0){
                let prefix = this.url.includes('?') ? "&" : "?";
                uri +=  `${prefix}${this.url_parameter}`;
            }

            if(this.current_link != this.url) this.current_link = uri;
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            if (this.paginate !== 10) uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
            if (pdf != null) uri = this.Cts.fillUrlParameters(uri, 'pdf', 1);
            if (this.sort.length > 0 && this.sort_direction.length > 0) {
                uri = this.Cts.fillUrlParameters(uri, 'sort', this.sort);
                uri = this.Cts.fillUrlParameters(uri, 'direction', this.sort_direction);
            }
            return uri;
        },
        setSelection: function (input, value) {
            value = { 'id': value.id, 'name': value.name, 'tag': input }
            if (!this.multiple) {
                this.selected = [value];
                return;
            }
            let checked = false;
            if(input.currentTarget !== undefined && input.currentTarget.checked === true) checked = true;
            if (input.checked === true) checked = true;
            if (checked === true) {
                var existValue = this.selected.some(function(item) {
                    return item.id === value.id;
                });
                if (!existValue) {
                    this.selected.push(value);
                }

            } else {
                this.selected = this.selected.filter(item => item.id !== value.id);
            }

        },
        removeSelected: function (selected) {
            this.selected = this.selected.filter(item => item.id !== selected.id);
            if (selected.tag !== undefined && selected.tag !== null && selected.tag.explicitOriginalTarget !== undefined ){
                selected.tag.explicitOriginalTarget.checked = false;

            }else{
                let check = this.$el.querySelector("#inp_"+selected.id);
                check.checked = false;
            }

        },
        setSelectionByRow: function (id) {
            if (this.name_choose == null) return;
            let el = this.$el.querySelector("#" + id);
            if (el != null) {
                el.click();
            }
        },
        selectAndSend: function (id) {
            if (!this.multiple) {
                this.setSelectionByRow(id);
                let btnSelector = this.$el.querySelector('#markAsSelectedButton');
                if (btnSelector != null) btnSelector.click();
            }
        },
        formatJson: function (text){
            if(text== null) return "";
            return (typeof text === 'object') ? `${text.id} : ${text.name}` : text;
        },
        setSelectionAll: function () {
            if (this.name_choose == null) return;
            let el = this.$el.querySelectorAll(".checkbox_" + this.name_choose);
            let $this = this;
            if (el != null) {
                let checkAll = this.$el.querySelector("#checkAll");
                if (checkAll && checkAll.checked === true){

                    if (this.pagination.total != undefined && this.pagination.total > this.pagination.per_page) {
                        let the_uri = this.encodeURL('');
                        let prefix = the_uri.includes('?') ? "&" : "?";
                        the_uri +=  prefix+'all_item=true';
                        this.progress = true;
                        fetch(the_uri).then(response => response.json()).then((data) => {
                            this.items_list = data;
                        }).finally(() => {

                            this.items_list.forEach((sel) => {
                                let value = {'id': sel.id, 'name': sel.name,}
                                var existValue = this.selected.some(function(item) {
                                    return item.id === value.id;
                                });
                                if (!existValue) {
                                    this.selected.push(value);
                                }
                            });
                            this.progress = false;
                            this.selectedAll = true;

                        });
                    }else{
                        el.forEach(function(checkbox) {
                            let name = checkbox.getAttribute('data-name-item');
                            let checkboxId = checkbox.id.split('inp_')[1];
                            let value = {'id': Number(checkboxId), 'name': name, 'tag': checkbox};
                            checkbox.setAttribute('checked', true);
                            checkbox.checked = true;
                            $this.setSelection(checkbox,value);
                        });
                        this.selectedAll = true;
                    }

                }else{
                    this.selectedAll = false;
                    this.selected = [];
                }

            }
        },
        checkedVerify: function (currentItem) {
            var existValue = this.selected.some(function(item) {
                return item.id === currentItem.id;
            });
            return this.selectedAll && existValue;
        }
    }
}
</script>
<style scoped>
.possible-selection {
    cursor: pointer;
}

.img-card {
    width: 200px;
    height: 100px;
    background-repeat: no-repeat;
    background-position: center center;
    background-size: contain;
    cursor: pointer;
}

.possible-selection:hover {
    background-color: lightgrey;
}

.list_selected{
    overflow-y: auto;
    max-height: 138px;
}
</style>
