<template>
    <div class="container-alert">
        <div v-if="alertStore.error != ''" class="alert alert-danger alert-dismissible fade show" role="alert">
            <span aria-controls="collapseMessage" aria-expanded="false" data-bs-target="#collapseMessage"
                  data-bs-toggle="collapse" style="cursor:pointer" v-on:click="changeCollapse">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ $t('An error occurred while saving the data') }}
                <i :class="'fa-solid fa-caret-' + collapse" style="padding:10px; "></i>
            </span>
            <div id="collapseMessage" class="collapse collapse-vertical error-details">
                <div class="card card-body"> {{ alertStore.error }}</div>
            </div>
            <button aria-label="Close" class="btn-close"  type="button"
                    v-on:click="cleanMessage('error')"></button>
        </div>
        <div v-if="alertStore.success != ''" class="alert alert-success dispel_hidden" role="alert">
            <div>
                <i class="fa-solid fa-check"></i> {{ alertStore.success }}
            </div>
            <button aria-label="Close" class="btn-close" type="button"
                    v-on:click="cleanMessage('success')"></button>
        </div>
    </div>
</template>


<script lang="ts" src="./AlertComponent.ts"/>

<style scoped src="./AlertComponent.css"/>
