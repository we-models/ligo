<template>
    <div>

        <div v-cloak class="vue-layout">
            <ul class="nav nav-pills all_tabs" role="tablist">
                <li v-if="permissions.includes('.create')" class="nav-item" role="presentation">
                    <button id="create-tab" aria-controls="create-tab-pane" aria-selected="true"
                            class="nav-link active" data-bs-target="#create-tab-pane" data-bs-toggle="tab" role="tab"
                            type="button" v-on:click="setSelected('.create')">
                        <span class="tab-title">{{ $t('create') }} {{ $t('new') }} {{ $t(title.toLowerCase()) }} </span>
                    </button>
                </li>
                <li v-if="permissions.includes('.all')" class="nav-item" role="presentation">
                    <button id="list-tab" aria-controls="list-tab-pane" aria-selected="false" class="nav-link"
                            data-bs-target="#list-tab-pane" data-bs-toggle="tab" role="tab" type="button"
                            v-on:click="setSelected('.all')">
                        <span>{{ t(title.toLowerCase(), 2) }}</span>
                    </button>
                </li>
                <li v-if="permissions.includes('.logs')" class="nav-item" role="presentation">
                    <button id="logs-tab" aria-controls="logs-tab-pane" aria-selected="false" class="nav-link"
                            data-bs-target="#logs-tab-pane" data-bs-toggle="tab" role="tab" type="button"
                            v-on:click="setSelected('.logs')">
                        <span>{{ $t('logs') }}</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div v-if="permissions.includes('.create')" id="create-tab-pane" aria-labelledby="create-tab"
                     class="tab-pane fade show active" tabindex="0">

                    <template v-if="selected === '.create'">
                        <template v-if="isObject !== '1'">
                            <form-component  :csrf="csrf"
                                :custom_fields="this.custom_fields"
                                :fields="fields" :icons="icons" :object="object_"
                                :title="t(title.toLowerCase(), 1)"
                                :url="create"
                                :prefix = "$t('Register new')"
                                http_method="POST"/>
                        </template>
                        <template v-else>
                            <form-object-component
                                :csrf="csrf"
                                :fields="fields"
                                :custom_fields="this.custom_fields"
                                :object="object_"
                                :title="t(title.toLowerCase(), 1)"
                                :url="create"
                                :prefix = "$t('Register new')"
                                http_method="POST"
                            />
                        </template>
                    </template>

                </div>
                <div v-if="permissions.includes('.all')" id="list-tab-pane" aria-labelledby="list-tab"
                     class="tab-pane fade form-div"
                     role="tabpanel" tabindex="0">
                    <list-component v-if="selected === '.all'" :csrf="csrf"
                                    :fields="fields" :index="index" :object="object_" :object_class="object"
                                    :permissions="permissions" :url="all"/>
                </div>
                <div v-if="permissions.includes('.logs')" id="logs-tab-pane" aria-labelledby="logs-tab"
                     class="tab-pane fade form-div"
                     role="tabpanel" tabindex="0">
                    <logs-component v-if="selected === '.logs'" :url="this.logs"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./CrudComponent.ts"/>

<style scoped src="./CrudComponent.css"/>
