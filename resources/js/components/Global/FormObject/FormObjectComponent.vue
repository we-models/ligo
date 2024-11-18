<script lang="ts" src="./FormObjectComponent.ts"/>
<style scoped src="./FormObjectComponent.css"/>

<template>
    <div :class="'form-flex ' + (custom_fields.length > 0 ? 'full-large': 'half-large')">
        <template v-if="!isReadOnly">
            <alert-component/>
            <progress-component :progress="this.progress"/>
            <h4 class="crud-title">{{ prefix + ' ' + $t(title) }}</h4>
            <form ref="FormComponentRef" :action="url" :method="http_method" @submit.prevent="this.save">
                <fieldset :disabled="isReadOnly" class="flex-form">
                    <input v-model='this.csrf' name="_token" type="hidden"/>
                    <div v-for="(v, k) in fields" :style="getColumnWidth(v)" class="flex-form-item">
                        <template v-if="this.getCondition(v.attributes, k)">
                            <div v-if="currentObject != null" class="form-group">
                                <div>
                                    <label v-if="!Cts.isBool(v.attributes.type)" :for="`${k}`" class="form-label">
                                        {{ $t(v.properties.label) }}
                                    </label>
                                    <div v-else class="form-check form-switch">
                                        <input v-if="v.attributes.type === 'checkbox'" :id="'id_' + k"
                                               v-model="currentObject[k]" :name="k" v-bind="v.attributes">
                                        <label :for="'id_' + k" class="form-check-label form-label">
                                            {{ $t(v.properties.label) }} </label>
                                    </div>
                                </div>
                                <template v-if="Cts.isText(v.attributes.type)">
                                    <template v-if="isNameNoEditable">
                                        <label class="only-label">{{ currentObject[k] }}</label>
                                    </template>
                                    <input v-else :id="k" v-model="currentObject[k]" :name="k" v-bind="v.attributes">
                                </template>

                                <object-selector-component
                                    v-if="Cts.isObject(v.attributes.type) || Cts.isMediaFile(v.attributes.type)"
                                    :csrf="csrf" :depends="getDepends(this.fields[k])"
                                    :ent="object"
                                    :llave="k"
                                    :multiple="this.fields[k].attributes.multiple"
                                    :paginate="12"
                                    :readonly="isReadOnly || this.getReadOnly(this.fields[k])"
                                    :required="this.fields[k].attributes.required"
                                    :type="v.attributes.type"
                                    :value="v.attributes"
                                    @onSetSelectedItems="markFieldSelected"
                                />

                                <div v-if="v.attributes.type === 'select'">
                                    <select :id="'id_' + k" v-model="currentObject[k]" :name="`${k}`"
                                            v-bind="getBindAttributes(v.attributes)">
                                        <option v-for="(option, option_id) in optionsShowSelect(v.attributes)"
                                                :value="option_id">{{ $t(option) }}
                                        </option>
                                    </select>
                                </div>

                                <textarea v-if="v.attributes.type === 'textarea'" id="" v-model="currentObject[k]"
                                          :name="`${k}`" class="form-control" cols="30" rows="4"></textarea>

                                <field-component v-if="v.attributes.type === 'variable'" :ent="object[k]" :name="k"
                                                 :type="object[v.attributes.decision]" @onSetSelectedItems="markFieldSelected" />

                            </div>
                        </template>
                    </div>
                </fieldset>
            </form>
        </template>
    </div>
</template>


