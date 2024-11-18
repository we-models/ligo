<script lang="ts" src="./NavBarComponent.ts" />

<style scoped src="./NavBarComponent.css" />
<template>
    <nav class="navBar">
        <div class="logo-img">
            <img alt="" src="/image_system/navbar/ligo.png" />
        </div>
        
        <div class="close-session flex-1">
            <a :href="url">
                {{ t("logOut") }}
            </a>
        </div>
    </nav>
</template>
