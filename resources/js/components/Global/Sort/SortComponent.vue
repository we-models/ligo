<script lang="ts" src="./SortComponent.ts"/>
<style scoped src="./SortComponent.css"/>

<template>
    <div class="row-title">

        <div v-if="!isUndefined" class="list-title">
            {{ $t(fields[theKey].properties.label) }}
        </div>

        <div v-if="isUndefined" class="list-title">
            {{ $t(`${theKey}`) }}
        </div>

        <div v-if="!isUndefined && isSortable" class="sort-buttons">
            <a href="javascript:void(0)" v-on:click="refillDirection('asc')">
                <span>
                    <i :class="directionUp"/>
                </span>
            </a>
            <a href="javascript:void(0)" v-on:click="refillDirection('desc')">
                <span>
                    <i :class="directionDown"/>
                </span>
            </a>
        </div>
    </div>
</template>
