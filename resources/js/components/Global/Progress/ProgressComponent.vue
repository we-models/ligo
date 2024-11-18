<template>
    <div class="progress">
        <div :aria-valuenow="progress" :style="'width: ' + progress + '%;'" aria-label="{{$t('Progress')}}"
             aria-valuemax="100" aria-valuemin="0" class="progress-bar" role="progressbar">
        </div>
    </div>
</template>

<script lang="ts" src="./ProgressComponent.ts"/>

<style scoped src="./ProgressComponent.css"/>
