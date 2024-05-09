<script lang="ts" src="./PaginationComponent.ts"/>
<style scoped src="./PaginationComponent.css"/>

<template>
    <nav v-if="pagination.last_page > 1" aria-label="Page navigation" class="nav all-pages">
        <ul class="pagination">
            <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                <button v-if="link.url != null" class="page-link" v-on:click="changePage(link.url)"
                        v-html="getPage(link.label)" type="button"></button>
            </li>
        </ul>
    </nav>
</template>
