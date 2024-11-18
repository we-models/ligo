<template>
    <div :class="'form-flex ' + (custom_fields.length > 0 ? 'full-large': 'half-large')">
        <alert-component v-if="!isReadOnly" />
        <progress-component v-if="!isReadOnly" :progress="this.progress" />

        <h4 class="crud-title">{{ prefix + ' ' + $t(title) }}</h4>
        <form :action="url" :method="method" @submit.prevent="this.save" ref="FormComponentRef">
            <fieldset :disabled="isReadOnly" class="flex-form" >

                <input v-model='this.csrf' name="_token" type="hidden" />

                <div v-for="(v, k) in all_fields" class="flex-form-item" :style="getColumnWidth(v)" >
                    <div v-if="this.getCondition(v.attributes, k)" class="form-group">
                        <!-- Input for CheckBox and RadioButton -->
                        <div>
                            <label v-if="!Cts.isBool(v.attributes.type)" :for="`${k}`" class="form-label">
                                {{ $t(v.properties.label) }}
                            </label>
                            <div v-if="Cts.isBool(v.attributes.type)" class="form-check form-switch">
                                <input v-if="v.attributes.type === 'checkbox'" :id="'id_' + k"
                                       :checked="entity[k]" :name="k" v-bind="v.attributes">
                                <label :for="'id_' + k" class="form-check-label form-label">
                                    {{ $t(v.properties.label) }} </label>
                            </div>
                        </div>

                        <!-- Input for text, mail, tel, number, url -->

                        <template v-if="Cts.isText(v.attributes.type)">
                            <template v-if="entity?.object_type !== undefined && entity?.object_type?.editable_name === 0">
                                <label class="only-label" >{{ entity[k] }}</label>
                            </template>

                            <input v-else  :id="k" v-model="entity[k]" :name="k"
                            v-bind="v.attributes">
                        </template>
                        <object-selector-component
                            v-if="Cts.isObject(v.attributes.type) || Cts.isMediaFile(v.attributes.type)"
                            :csrf="csrf" :depends="getDepends(this.all_fields[k])"
                            :ent="this.entity"
                            :llave="k"
                            :multiple="this.all_fields[k].attributes.multiple"
                            :paginate="12"
                            :readonly="isReadOnly || this.getReadOnly(this.all_fields[k])"
                            :required="this.all_fields[k].attributes.required"
                            :type="v.attributes.type"
                            :value="v.attributes"
                            @onSetSelectedItems="markFieldSelected"
                        />

                        <div v-if="v.attributes.type === 'select'">
                            <select :id="'id_' + k" v-model="entity[k]" :name="`${k}`"
                                    v-bind="getBindAttributes(v.attributes)">
                                <option v-for="(option, option_id) in optionsShowSelect(v.attributes)"
                                        :value="option_id">{{ $t(option) }}
                                </option>
                            </select>
                        </div>

                        <textarea v-if="v.attributes.type === 'textarea'" id="" v-model="entity[k]"
                                  :name="`${k}`" class="form-control" cols="30" rows="4"></textarea>

                        <field-component v-if="v.attributes.type === 'variable'" :ent="entity[k]" :name="k"
                                         :type="entity[v.attributes.decision]" @onSetSelectedItems="markFieldSelected" />
                    </div>
                </div>

                <template v-if="custom_fields.length > 0">
                    <div v-for="(field,key) in this.custom_fields" class="flex-form-item" :style="getColumnWidth(field.width)">
                        <div v-if="isRelationOrMediaFile(field)">
                            <div class="form-group">
                                <label :for="field.slug">
                                    {{ field.name }}
                                    <strong v-if="globalStore.APP_DEBUG == 'true'">
                                        ({{ field.slug }})
                                    </strong>
                                </label>
                                <object-selector-component
                                    :accept="field.accept === undefined ? '' : field.accept"
                                    :csrf="csrf" :depends="['filling_method=' + field.filling_method]"
                                    :ent="field.entity" :field_id="field.id" :field_layout="field.layout"
                                    :filling_method="field.filling_method" :llave="field.slug"
                                    :multiple="(fieldAttributes(field)).isMultiple" :paginate="12"
                                    :readonly="isReadOnly || this.getReadOnlyCustomFields(this.custom_fields[key])"
                                    :required="field.data?.required" :type="(fieldAttributes(field)).object"
                                    :value="field.data" />
                            </div>
                        </div>
                        <div v-if="field.status === 'field'">
                            <div v-if="field.layout === 'tab'">
                                <div class="form-group">
                                    <div class="card">
                                        <div :id="'cf_heading_' + field.id" class="card-header">
                                            <button class="btn btn-link" type="button"
                                                    v-on:click="toggleCF('#cf_collapse_' + field.id)">
                                                {{ field.name }}
                                            </button>
                                        </div>

                                        <div :id="'cf_collapse_' + field.id"
                                             class="collapse show">
                                            <div class="card-body card-tab-flex" v-if="loaded">
                                                <div v-for="tab_field in field.fields" :style="getColumnWidthForTab(tab_field.width, field.id)">
                                                    <div class="form-group" >
                                                        <label for="">{{ tab_field.name }}
                                                            <strong v-if="globalStore.APP_DEBUG == 'true'">({{ tab_field['slug'] }})</strong> </label>

                                                        <field-component
                                                            v-if="isField(tab_field)"
                                                            :ent="this.getValue(tab_field)" :field="tab_field"
                                                            :name="tab_field.slug" :readonly="isReadOnly"
                                                            :type="tab_field.type" />

                                                        <object-selector-component
                                                            v-if="isRelationOrMediaFile(tab_field)"
                                                            :accept="tab_field.accept === undefined ? '' : tab_field.accept"
                                                            :csrf="csrf"
                                                            :depends="['filling_method=' + tab_field.filling_method]"
                                                            :ent="tab_field.entity" :field_id="tab_field.id"
                                                            :field_layout="tab_field.layout"
                                                            :filling_method="tab_field.filling_method"
                                                            :llave="tab_field.slug"
                                                            :multiple="(fieldAttributes(tab_field)).isMultiple"
                                                            :paginate="12" :readonly="isReadOnly"
                                                            :required="false"
                                                            :type="(fieldAttributes(tab_field)).object"
                                                            :value="tab_field.data" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div v-if="isField(field)"
                                 class="form-group">
                                <label for="">
                                    {{ field.name }}
                                    <strong v-if="globalStore.APP_DEBUG == 'true'"> ({{ field.slug }}) </strong>
                                </label>

                                <field-component :ent="this.getValue(field)" :name="field.slug"
                                                 :type="field.type" />
                            </div>
                        </div>
                    </div>
                </template>

            </fieldset>

            <fieldset v-if="!isReadOnly">
                <div class="col d-md-flex justify-content-md-end">
                    <button class="btn btn-dark" type="submit">{{ $t('Save') + " " + $t(title) }}</button>
                </div>
            </fieldset>
        </form>
    </div>
</template>

<script lang="ts" src="./FormComponent.ts" />

<style scoped src="./FormComponent.css" />
