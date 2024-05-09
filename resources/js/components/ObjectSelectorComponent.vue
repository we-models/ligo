<template>
    <div>
        <div class="input-group">
            <div class="form-control" style="height: auto;display: flex;">
                <span  v-if="!readonly && (filling_method == 'creation' || filling_method == 'all')" style="margin-right: 7px">
                    <a href="javascript:void(0)" v-on:click="openModalForm(value.data.index)"  style="width: 100%"><i class="fa-solid fa-circle-plus"></i></a>
                </span>

                <div v-if="entity[theKey] != null"  :class="this.type === 'image' ? 'media-container' : ''">

                    <div v-if="this.multiple" v-for="option in entity[theKey]" >
                        <img :src="option.thumbnail" :alt="option.name" class="image" v-if="this.type === 'image'">
                        <label for="">
                            <i v-if="!readonly" class="fa-solid fa-xmark remove_item" v-on:click="removeItem(option)"></i>
                            {{ option.name }}
                        </label>
                        <input type="hidden" :name="theKey + '[]'" v-model="option.id" />
                    </div>

                    <div v-if="!this.multiple" >
                        <div v-if="!Array.isArray(entity[theKey])" >
                            <img :src="entity[theKey].thumbnail" :alt="entity[theKey].name" class="image" v-if="this.type === 'image'">

                            <label for="">
                                <i v-if="!readonly && required" class="fa-solid fa-xmark remove_item" v-on:click="removeFromSelector(theKey)"></i>
                                <strong v-on:click="openModalGeneral(value, theKey)">
                                    {{  entity[theKey].name }}
                                </strong>
                            </label>


                            <input type="hidden" :name="theKey" v-model="entity[theKey].id" />
                        </div>
                    </div>

                </div>
            </div>
            <span class="input-group-text" v-if="!readonly && filling_method != '' && filling_method != 'creation'">
                <a href="javascript:void(0)" v-on:click="openModalGeneral(value, theKey)" style="width: 100%">{{ $t('Select') }}</a>
            </span>
        </div>
        <div>
            <div class="modal fade" :id="modal_id" data-bs-theKeyboard="false" tabindex="-1"
                 :aria-labelledby="modal_id + 'Label'" aria-hidden="true">
                <div class="modal-dialog modal-xl" v-if="this.modal_open">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>{{ $t('Select from these options') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <list-component
                                v-if="this.type === 'object'"
                                :object="this.current.data.values"
                                :fields="this.current.data.fields"
                                :url="getDependences(this.current.data.url)"
                                :csrf="csrf"
                                :permissions="[]"
                                :multiple="this.multiple"
                                :name_choose="this.current.name"
                                @onSelected="markSelected"
                                :itemsSelected="this.entity[this.theKey]"
                                :req="required" />

                            <image-component
                                v-if="this.type === 'image'"
                                :post="this.value.data.store"
                                :csrf="this.csrf"
                                :url="this.value.data.url"
                                :multiple="this.value.multiple"
                                :sorts="this.value.data.sorts"
                                :quantity="this.paginate"
                                :selectable="true"
                                :setChosen="this.markSelected"
                                :itemsSelected="this.entity[this.theKey]" />

                            <file-component
                                v-if="this.type === 'file'"
                                :post="this.value.data.store"
                                :csrf="this.csrf"
                                :url="this.value.data.url"
                                :multiple="this.value.multiple"
                                :sorts="this.value.data.sorts"
                                :quantity="this.paginate"
                                :selectable="true"
                                :accept = "this.accept"
                                :setChosen="this.markSelected"
                                :field_id="this.field_id"
                                :field_layout = "this.field_layout"
                                :itemsSelected="this.entity[this.theKey]" />

                            <div v-if="error != null">
                                <strong>{{ error }}</strong>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:void(0)" v-on:click="closeModal"><strong>{{ $t('Cancel') }}</strong></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="modal fade" :id="modal_form_id" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="formEditModalLabel" aria-hidden="true" >
                <div class="modal-dialog modal-xl" style="min-width: 80vw;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModalForm"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div style="max-width: 1200px; margin:0 auto">
                                <div class="interface_loader" v-if="dataModal.loading"></div>
                                <!-- <form-component :http_method="method" v-if="object_ != null && !loading" :object="object_"
                                    :fields="fields" :url="url" :icons="icons" :csrf="csrf" :title="title" :custom_fields="custom_fields"/> -->
                                <form-component
                                    v-if=" !dataModal.loading"
                                    http_method="POST"
                                    :object="this.dataModal.values"
                                    :fields="value.data.fields"
                                    :url="dataModal.create"
                                    :icons="dataModal.icons"
                                    :csrf="this.csrf"
                                    :custom_fields = "this.dataModal.custom_fields"
                                    :itemsSelected="this.entity[this.theKey]"
                                    @onSelected="markCreated"
                                    :title="$t('Register new') + ' ' + $tChoice(dataModal.title.toLowerCase(), 1)" />
                                <div v-if="error != null">
                                    <strong>{{ error }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:void(0)" v-on:click="closeModalForm"><strong>{{ $t('Cancel') }}</strong></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { v4 as uuidv4 } from 'uuid';
export default {
    props: ['ent', 'llave', 'readonly', 'required', 'value', 'csrf', 'multiple', 'paginate', 'type', 'depends', 'accept', 'field_id', 'field_layout','filling_method'],
    name: "ObjectSelectorComponent",
    data(){
        return {
            entity: this.ent,
            modal_id: uuidv4().toString(),
            modal_form_id: uuidv4().toString(),
            current : null,
            modal_open: false,
            reloaded : false,
            theKey : this.llave,
            error : null,

            dataModal: {
                title: "",
                csrf: "",
                fields: [],
                icons: [],
                values: {},
                index: "",
                all: "",
                create: "",
                languages: [],
                language: "en",
                permissions: [],
                logs: "",
                loaded : false,
                custom_fields : [],
                loading: false
            }
        }
    },
    mounted() {
        if(!this.multiple && Array.isArray(this.entity[this.theKey])   ){
            this.entity[this.theKey] = this.entity[this.theKey][0];
        }
    },
    methods: {
        closeModal: function () {
            this.modal_open = false;
            jQuery('#' + this.modal_id).hide();
            this.current = null;
        },
        openModalGeneral: function (value, theKey) {
            if (this.readonly) return;
            this.current = value;
            this.modal_open = true;
            jQuery('#' + this.modal_id).show();
        },
        markSelected: function (selected) {
            this.closeModal();
            selected = !this.multiple ? selected[0] : selected;
            this.entity[this.theKey] = selected;
            this.$emit('onSetSelectedItems', this.theKey, selected);
        },
        removeItem: function (op) {
            this.entity[this.theKey] = this.entity[this.theKey].filter(item => item.id !== op.id);
            this.$emit('onSetSelectedItems', this.theKey, this.entity[this.theKey]);
        },
        getDependences : function(uri){
            for(let i=0; i < this.depends.length; i++){
                uri+= ((uri.includes('?') ? '&' : '?') + this.depends[i] );
            }
            return uri;
        },
        removeFromSelector : function(item){
            this.entity[item] = null;
            this.$emit('onSetSelectedItems', this.theKey, null);
        },
        openModalForm: function (link) {

            var uri_separator = link.split('?');
            var index = uri_separator[0];
            var uri = index + '/details';
            if (uri_separator.length > 1) {
                uri += "?".concat(uri_separator[1]);
            }
            this.dataModal.loading = true;
            fetch(uri)
                .then(response => response.json())
                .then((data) => {
                    this.dataModal.title = data.title;
                    this.dataModal.csrf = data.csrf;
                    this.dataModal.fields = data.fields;
                    this.dataModal.icons = data.icons;
                    this.dataModal.values = data.values;
                    this.dataModal.index = data.index;
                    this.dataModal.all = data.all;
                    this.dataModal.create = data.create;
                    this.dataModal.languages = data.languages;
                    this.dataModal.language = data.language;
                    this.dataModal.permissions = data.permissions;
                    this.dataModal.logs = data.logs;
                    this.dataModal.custom_fields = data.custom_fields;

                }).finally(()=>{
                    this.dataModal.loading = false;
            });

            jQuery('#' + this.modal_form_id).show();

            // this.emitter.emit("fillModalCRUD", { 'index': link, 'id': id, 'method': method });
        },
        closeModalForm: function () {
            jQuery('#' + this.modal_form_id).hide();
        },
        markCreated: function (selected) {
            this.closeModalForm();
            if (!this.multiple) {
                selected = selected[0];
            } else {
                var currentSelected = this.entity[this.theKey];
                currentSelected.push({id: selected[0].id, name: selected[0].name});
                selected = currentSelected;
            }
            // selected = !this.multiple ? selected[0] : selected;
            this.entity[this.theKey] = selected;
            this.$emit('onSetSelectedItems', this.theKey, selected);
        },
    }
}
</script>

<style scoped>
    .media-container {
        display: flex;
        gap: 20px;
        flex-direction: column;
    }
    .image {
        width: 50px;
        margin-right:12px
    }
</style>
