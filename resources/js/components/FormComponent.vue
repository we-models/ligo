<template>
    <div>
        <alert-component :listener="alert_listener" v-if="!readonly" />

        <h4 class="crud-title">{{title}}</h4>
        <progress-component :progress="this.progress" v-if="!readonly"/>

        <form :action="url" :method="method" @submit.prevent="this.save" >

            <div class="row">
                <div :class="(custom_fields.length > 0 ? 'col-lg-5' : 'col-lg-9') + ' col-xs-12'">
                    <fieldset :disabled = "readonly" >

                        <input type="hidden" name="_token" v-model='this.csrf' />

                        <div class="row">
                            <div v-for="(v, k) in all_fields" :class = "getColumnWidth(v)"   >

                                <div  class="form-group" v-if="this.getCondition(v.attributes, k)">
                                    <!-- Input for CheckBox and RadioButton -->
                                    <div>
                                        <label v-if = "!Cts.isBool(v.attributes.type) " :for="k" class="form-label">
                                            {{$t(v.properties.label)}}
                                        </label>
                                        <div v-if = "Cts.isBool(v.attributes.type) " class="form-check form-switch">
                                            <input v-bind="v.attributes" v-if = "v.attributes.type === 'checkbox'" :checked="entity[k]" :name="k" :id = "'id_' + k" >
                                            <label class="form-check-label" :for="'id_' + k"> {{$t(v.properties.label)}} </label>
                                        </div>
                                    </div>



                                    <!-- Input for text, mail, tel, number, url -->

                                    <input v-if="Cts.isText(v.attributes.type)" v-model="entity[k]" :name="k" v-bind="v.attributes" :id="k">
                                    <object-selector-component
                                        v-if="Cts.isObject(v.attributes.type) || Cts.isMediaFile(v.attributes.type)"
                                        :ent = "this.entity"
                                        :llave="k"
                                        :value = "v.attributes"
                                        :csrf = "csrf"
                                        :required = "this.all_fields[k].attributes.required"
                                        :readonly = "readonly || this.getReadOnly(this.all_fields[k])"
                                        :multiple="this.all_fields[k].attributes.multiple"
                                        :paginate = "12"
                                        :type = "v.attributes.type"
                                        :depends = "getDepends(this.all_fields[k])"
                                        @onSetSelectedItems = "markFieldSelected"
                                    />

                                    <div v-if="v.attributes.type === 'select'">
                                        <select  v-bind="getBindAttributes(v.attributes)" :name="k" :id = "'id_' + k" v-model="entity[k]">
                                            <option :value="option_id" v-for="(option, option_id) in v.attributes.options ">{{option}}</option>
                                        </select>
                                    </div>

                                    <div v-if="entity[v.attributes.selector] != null">
                                        <array-json-component
                                            v-if="v.attributes.type === 'Array' && entity[v.attributes.selector].name === 'Select' "
                                            :ent="entity[k].length === 0 ? '[]' : entity[k]"
                                            :theKey="k" />
                                    </div>
                                    <!-- Input for Icons -->

                                    <vueSelect
                                        :disabled="readonly"
                                        v-if="v.attributes.type === 'icon'"
                                        v-model="entity[k]"
                                        :name="k"
                                        :options = Cts.allIcons(icons) >
                                        <template v-slot:selected-option="option">
                                            <input v-bind="v.attributes" type="hidden" :name="k" v-model="entity[k]"  >
                                            <i :class="option.label + ' option_select'"></i>
                                            {{ option.label }}
                                        </template>
                                        <template v-slot:option="option">
                                            <i :class="option.label + ' option_select'"></i>
                                            {{ option.label }}
                                        </template>
                                    </vueSelect>

                                    <!-- Input for TextArea -->

                                    <text-area-component
                                        v-if = "v.attributes.type === 'textarea' && isQuill(v.attributes)"
                                        :ent="entity[k]"
                                        :theKey ="k"
                                        :toolbar="Cts.quill_toolbar"
                                    />

                                    <textarea
                                        v-if = "v.attributes.type === 'textarea' && !isQuill(v.attributes)"
                                        :name="k"
                                        id=""
                                        cols="30"
                                        class= "form-control"
                                        rows="10" v-model="entity[k]"></textarea>

                                    <!-- Input for Variable input -->

                                    <field-component
                                        v-if = "v.attributes.type === 'variable'"
                                        :ent="entity[k]"
                                        :name = "k"
                                        :type = "entity[v.attributes.decision]"
                                        @onSetSelectedItems = "markFieldSelected"
                                    />
                                </div>
                            </div>
                        </div>

                    </fieldset>
                </div>
                <div class="col-lg-7" v-if="custom_fields.length > 0 ">
                    <h5>{{$t('Custom fields')}}</h5>
                    <fieldset  :disabled = "readonly" >
                        <div v-for="field in this.custom_fields">
                            <div v-if="field.status === 'relation' || ( field.layout === 'tab' &&( ['Image', 'File'].includes(field.type.name)))">
                                <div class="form-group">
                                    <label :for="field.slug">{{field.name}}  <strong>({{field.slug}})</strong>  </label>
                                    <object-selector-component
                                        :ent = "field.entity"
                                        :llave="field.slug"
                                        :value = "field.data"
                                        :csrf = "csrf"
                                        :required = "false"
                                        :readonly = "readonly"
                                        :multiple="(fieldAttributes(field)).isMultiple"
                                        :paginate = "12"
                                        :accept="field.accept === undefined ? '' : field.accept"
                                        :type = "(fieldAttributes(field)).object"
                                        :depends = "['filling_method='+field.filling_method]"
                                        :field_id="field.id"
                                        :field_layout = "field.layout"
                                        :filling_method = "field.filling_method"
                                    />
                                </div>
                            </div>
                            <div v-if="field.status === 'field'"  >
                                <div v-if="field.layout === 'tab'">
                                    <div class="form-group">
                                        <div class="card">
                                            <div class="card-header" :id="'cf_heading_' + field.id">
                                                <button v-on:click="toggleCF('#cf_collapse_' + field.id)" type="button" class="btn btn-link">
                                                    {{field.name}}
                                                </button>
                                            </div>

                                            <div :id="'cf_collapse_' + field.id" :class="'collapse ' + (readonly ? 'show': '')" >
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-4 col-md-6" v-for="tab_field in field.fields">
                                                            <div class="form-group">
                                                                <label for="">{{tab_field.name}} <strong>({{tab_field['slug']}})</strong> </label>

                                                                <field-component
                                                                    :ent="this.getValue(tab_field)"
                                                                    :name = "tab_field.slug"
                                                                    :type = "tab_field.type"
                                                                    :field = "tab_field"
                                                                    :readonly = "readonly"
                                                                    v-if="tab_field.status === 'field' && !['Image', 'File'].includes(tab_field.type.name)"
                                                                />

                                                                <object-selector-component
                                                                    :ent = "tab_field.entity"
                                                                    :llave="tab_field.slug"
                                                                    :accept="tab_field.accept === undefined ? '' : tab_field.accept"
                                                                    :value = "tab_field.data"
                                                                    :csrf = "csrf"
                                                                    :required = "false"
                                                                    :readonly = "readonly"
                                                                    :multiple="(fieldAttributes(tab_field)).isMultiple"
                                                                    :paginate = "12"
                                                                    :type = "(fieldAttributes(tab_field)).object"
                                                                    :depends = "['filling_method='+tab_field.filling_method]"
                                                                    :field_id="tab_field.id"
                                                                    :field_layout = "tab_field.layout"
                                                                    :filling_method = "tab_field.filling_method"
                                                                    v-if="tab_field.status === 'relation' || ['Image', 'File'].includes(tab_field.type.name) "
                                                                />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div v-if="field.layout === 'field' && !['Image', 'File'].includes(field.type.name)" class="form-group">
                                    <label for="">
                                        {{field.name}}
                                        <strong> ({{ field.slug }}) </strong>
                                    </label>

                                    <field-component
                                        :ent="this.getValue(field)"
                                        :name = "field.slug"
                                        :type = "field.type"
                                    />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="row" v-if="!readonly">
                <div class="col" v-if="method === 'POST'">
                    <button class="btn btn-info" v-on:click="clear" type="button">{{$t('Clear')}}</button>
                </div>
                <div class="col d-md-flex justify-content-md-end" style="text-align: right">
                    <button  class="btn btn-dark" type="submit">{{$t('Save')}}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { trans } from 'laravel-vue-i18n';
import cts from './Constants';

export default {
    props: ['object', 'fields', 'icons', 'csrf', 'title', 'url', 'http_method', 'custom_fields'],
    data(){
        return {
            Cts : cts,
            entity : this.object,
            all_fields : this.fields,
            progress : 0,
            alert_listener : new Date().getTime(),
            method : this.http_method,
            readonly : ['SHOW', 'DELETE'].includes(this.http_method),
            loading : false,
            error : null
        };
    },
    methods : {
        save : function (event){
            if(this.readonly) return;
            jQuery.ajax(this.url, {
                method: this.method,
                data: cts.formToJson(event),
                headers: { 'X-CSRF-TOKEN':  this.csrf },
                xhr: ()=> {
                    let xhr = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        this.progress = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    (new Audio(location.origin + '/sounds/success.mp3')).play();
                    this.emitter.emit("alert_" + this.alert_listener, {'success' : true, 'txt': trans('The data was saved successfully')});
                    var data = _response[1];
                    var value = { 'id': data.id, 'name': data.name };
                    var selected = [value];
                    this.$emit('onSelected', selected);

                    setTimeout(()=>{
                        this.emitter.emit("alert_" + this.alert_listener, {'success' : true, 'txt': null});
                    }, 4000);
                },
                error: (error)=> {
                    (new Audio(location.origin + '/sounds/error.ogg')).play();
                    this.emitter.emit("alert_" + this.alert_listener, {'success' : false, 'txt': error.responseText});
                },
                complete: ()=> {
                    this.progress = 0;
                }
            });
        },
        clear : function (){
            let quillForms = this.$el.querySelectorAll('.ql-editor');
            for(let i=0; i < quillForms.length; i++){
                quillForms[i].innerHTML = "";
            }
            this.entity = this.object;
            let firstInput = this.$el.querySelector('input:not([type = "hidden"])');
            firstInput.focus();
            firstInput.select();
        },
        markFieldSelected: function (k, s){
            this.entity[k] = s;
        },
        getReadOnly : function(fields){
            if(fields.attributes.hasOwnProperty('readonly')){
                return fields.attributes.readonly;
            }
            return false;
        },
        getColumnWidth : function(value){
            if(value.hasOwnProperty('properties')){
                return 'col-' + value.properties.width;
            }
            return 'col-12';
        },
        getCondition: function(v, k){
            let response = true;
            if(v.hasOwnProperty('conditions')){
                for(let i=0; i < v.conditions.length; i++){
                    let theValue = v.conditions[i].value;

                    let the_field = this.entity[v.conditions[i].field];
                    try{
                        the_field = ('id' in the_field) ? the_field.id : the_field;
                    }catch (error){

                    }

                    if(v.conditions[i].operation === '=' ){
                        if(the_field != theValue) {
                            response = false;
                        }
                    }

                    if(v.conditions[i].operation === '!=' ){
                        if(the_field == theValue) {
                            response = false;
                        }
                    }
                }

            }
            return response;
        },
        getBindAttributes : function(value){
            let response = {};
            Object.keys(value).forEach((key) => {
                if(key !== 'type' && key !== 'options'){
                    response[key] = value[key];
                }
            });
            return response;
        },
        getDepends : function (value){
            let response = [];
            if(value.attributes.depends !== undefined && value.attributes.depends != null){
                let depends = value.attributes.depends;
                for(let i= 0; i < depends.length; i++){
                    if(this.entity[depends[i].field] !== null){
                        response.push(`${depends[i].column}=${this.entity[depends[i].field].id}`);
                    }else{
                        response.push(`${depends[i].field}=${depends[i].column}`);
                    }
                }
            }
            return response;
        },
        toggleCF : function(id){
            $(id).collapse('toggle')
        },
        getValue : function (field){
            if(field.value == null ) return "";
            return field.value.value;
        },
        fieldAttributes : function (field){
            if(field.type.name !== undefined){
                return {
                    'isMultiple' : false,
                    'object' : (field.type.name).toLowerCase()
                }
            }else{
                return {
                    'isMultiple' : field.type === 'multiple',
                    'object' : 'object'
                }
            }
        },
        isQuill : function(input){
            if ( "isquill" in input){
                return input.isquill;
            }
            return true;
        }
    }
}
</script>
