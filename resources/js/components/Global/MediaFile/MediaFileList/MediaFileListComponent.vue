<template>
    <div>
        <div class="list-head">

            <div class="select-media-file-filter">
                <label for="sortFilter">{{ $t('Sort') }}</label>

                <select id="sortFilter" v-model="sort" class="form-select">
                    <option v-for="item in sorts" :value="item">{{ $t(item) }}</option>
                </select>
            </div>

            <div class="select-media-file-filter">
                <label  for="sortFilter">{{ $t('Order direction') }}</label>

                <select id="directionFilter" v-model="sort_direction" class="form-select">
                    <option value="desc">{{ $t('Descendent') }}</option>
                    <option value="asc">{{ $t('Upward') }}</option>
                </select>
            </div>

            <div class="search-top">
                <SearchComponent :page="page" :sort="sort" :sort_direction="sort_direction" :url="url" @onList="onList"
                @onProgress="onProgress" @onResetPage="onResetPage" />
            </div>

        </div>
        <div class="media-file-card-background">
            <div v-for="item in pagination.data" v-if="!progress">
                <div class="card media-file-card-container">
                    <button class="btn btn-dark show-details-button" type="button" v-on:click="showDetails(item)">
                        <i class="fa-solid fa-maximize"></i>
                    </button>
                    <div v-if="isImage" :class="'img-card' + (item.selected ? ' media-file-active' : '')"
                        :style="`background-image:url('` + item.url + `?size=small')`" v-on:click="setSelection(item)">
                    </div>
                    <div v-else :class="'file-card' + (item.selected ? ' media-file-active' : '')"
                        :style="`background-image:url('` + this.getImageFromFile(item)" v-on:click="setSelection(item)">
                    </div>
                </div>
                <div :class="[isImage ? 'image-item' : 'file-item']">
                    <p>
                        {{ item.name }}
                        <br>
                        <small>{{ (new Date(item.created_at)).toLocaleString() }}</small>
                    </p>
                </div>
            </div>

        </div>

        <InterfaceLoaderComponent v-if="progress" />

        <PaginationComponent :pagination="pagination" @onChange="changePage" />

        <nav v-if="multiple != null && selectable" class="nav justify-content-start">
            <ul class="pagination">
                <button id="markAsSelectedButton" :disabled="required && selected.length === 0" class="btn btn-primary"
                    type="button" @click="$emit('onSelected', selected)">
                    {{ $t('Mark as selected') }}
                </button>
            </ul>
        </nav>
    </div>
</template>

<script lang="ts" src="./MediaFileListComponent.ts" />
<style scoped src="./MediaFileListComponent.css" />
