<script lang="ts" src="./ListValueComponent.ts" />
<style scoped src="./ListValueComponent.css" />

<template>
    <div class="list-value-content">



        <div v-if="isUndefined && containsImage === false">
            {{ formatJson(item[theKey]) }}
        </div>

        <div v-if="!isUndefined && !isObject && !isImage" class="item-data"
            v-html="formatCell(fields[theKey], item[theKey])" />

        <div v-if="!isUndefined && isImage && item[theKey] !== null" class="item-definition">
            <div class="img-list">
                <div v-for="line in item[theKey]" :style="getImage(line.url)" class="img-card" />
            </div>
        </div>

        <div v-if="!isUndefined && isObject && item[theKey] !== null" class="item-definition">
            <div v-if="!Array.isArray(item[theKey])" class="item-data">
                <a href="javascript:void(0)" v-on:click="methodsLine(item[theKey].id)">
                    {{ $t(item[theKey].name) }}
                </a>
            </div>

            <div v-for="line in item[theKey]" v-if="Array.isArray(item[theKey])">
                <a href="javascript:void(0)" v-on:click="methodsLine(line.id)">
                    {{ line.name }}
                </a>
            </div>
        </div>

    </div>

</template>
