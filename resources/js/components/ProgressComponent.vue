<template>
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-label="{{$t('Progress')}}"
            :style="'width: ' + progress + '%;'" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
</template>

<script>
export default {
    name: "ProgressComponent",
    props: ['progress']
}
</script>

<style scoped>
.progress {
    height: 3px;
}
</style>
