<template>
    <div>
        <div class="title">
            <h3>{{ $t('Configurations') }}</h3>
        </div>
        <div class="vue-layout">
            <div v-if="selected != null" class="d-flex align-items-start">
                <div id="v-pills-tab" aria-orientation="vertical"
                     class="nav flex-column nav-pills nav-options"
                     role="tablist" v-if="!sizeStore.isTablet">
                    <button v-for="type_item in list_types"
                            :id="'v-pills-' + type_item['id'] + '-tab'"
                            :aria-controls="'v-pills-' + type_item['id']"
                            :aria-selected="type_item['id'] === selected.id"
                            :class="'nav-link ' + (type_item['id'] === selected.id ? 'active' : '')"
                            :data-bs-target="'#v-pills-' + type_item['id']" data-bs-toggle="pill"
                            role="tab"
                            type="button"
                            v-on:click="setTypeSelected(type_item)">
                        {{ $t(type_item['name']) }}
                    </button>
                </div>
                <div id="v-pills-tabContent" class="tab-content">
                    <div v-if="sizeStore.isTablet">
                        <div class="dropdown" style="text-align: center">
                            <button aria-expanded="false" class="btn btn-primary dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    type="button">
                                {{ $t(selected.name) }}
                            </button>
                            <ul class="dropdown-menu">
                                <li v-for="type_item in list_types">
                                    <a class="dropdown-item" href="#"
                                       v-on:click="setTypeSelected(type_item)">{{$t(type_item['name'])}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-if="loading" class="center loader">
                        <InterfaceLoaderComponent/>
                    </div>
                    <div v-else>
                        <div v-if="pagination.total === 0" class="loader" >
                            <h4 class="center">{{ $t('No data available') }}...</h4>
                        </div>
                        <div v-if="pagination.data !== undefined">
                            <div v-for="type_item in list_types"
                                 :id="'v-pills-' + type_item['id']"
                                 :aria-labelledby="'v-pills-' + type_item['id'] + '-tab'"
                                 :class="'tab-pane fade ' + (type_item['id'] === selected.id ? 'show active' : '')"
                                 role="tabpanel" tabindex="0">
                                <div v-if="type_item['id'] === selected.id" class="panel-scrolled">
                                    <div v-for="item in pagination.data"
                                         class="flex-container">
                                        <div style="flex-grow: 1">
                                            <div v-if="first.id === item.id || showTitle" class="flex-title">
                                                {{ $t('Name') }}
                                            </div>
                                            <div class="flex-content">
                                                {{ item.name }}
                                            </div>
                                        </div>
                                        <div style="flex-grow: 1">
                                            <div v-if="first.id === item.id || showTitle" class="flex-title">
                                                {{ $t('Description') }}
                                            </div>
                                            <div class="flex-content" v-html="item.description"></div>
                                        </div>
                                        <div class="conf-value">

                                            <div v-if="first.id === item.id || showTitle" class="flex-title">
                                                {{ $t('Value') }}
                                            </div>
                                            <div class="flex-content">

                                                <div
                                                    v-if="item.progress > 0"
                                                    :aria-valuenow="item.progress"
                                                    aria-label="Animated striped example"
                                                    aria-valuemax="100"
                                                    aria-valuemin="0"
                                                    class="progress"
                                                    role="progressbar"
                                                >
                                                    <div :style="'width: ' + item.progress + '%'"
                                                         class="progress-bar progress-bar-striped progress-bar-animated"></div>
                                                </div>

                                                <input v-if="Cts.input_types.includes(item.type.name)"
                                                       v-model="item.configuration.value" class="form-control"
                                                       v-bind="getTypeAttributes(item)">

                                                <div v-if="item.type.name === 'Boolean'" class="form-check form-switch">
                                                    <input v-model="item.configuration.value"
                                                           :checked="item.configuration.value === '1' || item.configuration.value === 'true' "
                                                           class="form-check-input"
                                                           type="checkbox">
                                                </div>

                                                <textarea v-if="item.type.name === 'Text'"
                                                          v-model="item.configuration.value"
                                                          :placeholder="item.default ?? ''"
                                                          class="form-control"/>

                                                <div class="btn-config">
                                                    <button class="btn btn-dark" type="button"
                                                            v-on:click="saveConfiguration(item)">{{ $t('Save') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>


                    <nav v-if="pagination.last_page > 1" aria-label="Page navigation" class="nav justify-content-end">
                        <ul class="pagination">
                            <li v-for="link in pagination.links"
                                v-bind:class="['page-item', link.active ? 'active' : '']">
                                <button v-if="link.url != null" class="page-link"
                                        v-on:click="getValuesFromLink(link.url)"
                                        v-html="$t(link.label)"></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./SystemConfigComponent.ts"/>

<style scoped src="./SystemConfigComponent.css"/>
