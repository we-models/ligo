<script lang="ts" src="./SearchComponent.ts"/>
<style scoped src="./SearchComponent.css"/>


<template>
    <div class="round-search">
        <span class="btn_link_span" v-on:click="onKeyUp('')">
            <i class="fa-solid fa-rotate" ></i>
        </span>
        <input
            v-model="search"
            :placeholder="$t('Search')"
            class="search_form"
            type="search"
            v-on:keyup="onKeyUp('')">
        <span class="btn_link_span" >
            <i class="fa-solid fa-magnifying-glass"></i>
        </span>

    </div>
</template>

