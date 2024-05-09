<template>

    <InterfaceLoaderComponent v-if="!loaded"/>
    <crud-component
        v-if="loaded"
        :all="this.all"
        :create="this.create"
        :csrf="this.csrf"
        :custom_fields="this.custom_fields"
        :fields="this.fields"
        :icons="this.icons"
        :index="this.index"
        :lng="this.language"
        :lngs="this.languages"
        :logs="this.logs"
        :object="this.object"
        :object_="this.values"
        :permissions="this.permissions"
        :title="this.title"
        :is-object="this.isObject"
    />

</template>

<script lang="ts" src="./GlobalView.ts"/>

<style scoped src="./GlobalView.css"/>
