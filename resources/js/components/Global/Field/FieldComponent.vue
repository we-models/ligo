<template>
    <div>
        <input v-if="Cts.input_types.includes(type.name)" v-model="entity" :name="name"
               :readonly="readonly" class="form-control"
               v-bind="getTypeAttributes()" v-on:input="changeValue">

        <div v-if="type.name === 'Boolean'" class="form-check form-switch">
            <input :checked="entity !== null && !['0', 0, false, ''].includes(entity.trim())" :name="name"
                   :readonly="readonly" class="form-check-input" type="checkbox"
                   v-on:change="changeValue">
        </div>

        <textarea v-if="type.name === 'Text'" v-model="entity" :name="name" :placeholder="default_ ?? ''"
                  :readonly="readonly" class="form-control"
                  v-on:input="changeValue" row="4">{{ entity }}</textarea>

        <map-component
            v-if="type.name === 'Map' && field !== undefined"
            :ent="renderLatLng()"
            :lat="field.latitude?.value"
            :lng="field.longitude?.value"
            :name="name"
            :readonly="readonly"
            :theKey="name"
            @onChange="changeLatLng">

        </map-component>

    </div>
</template>

<script lang="ts" src="./FieldComponent.ts"/>

<style scoped src="./FieldComponent.css"/>
