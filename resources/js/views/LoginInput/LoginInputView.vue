<template>
<div :class="[{ 'container-login-inputs': isActive }]">

    <div class="input-group mb-3 container-email">
        <span id="basic-addon1" class="input-group-text icon-input-left">
            <i class="fa-solid fa-user icon-user-style"></i>
        </span>
        <input
            id="email"
            v-model="value_email"
            :placeholder="t('user')"
            autocomplete="email"
            autofocus
            class="input-email form-control"
            name="email"
            required
            type="email"
        />
    </div>

    <div class="input-group container-password-default">
        <span id="basic-addon1" class="input-group-text icon-input-left">
            <i class="fa-solid fa-lock icon-lock-style"></i>
        </span>
        <input
            id="password"
            :placeholder="t(`${placeholderPass1}`)"
            :type="typeInput1"
            autocomplete="current-password"
            class="input-password form-control"
            name="password"
            required
        />

        <span id="basic-addon2" class="input-group-text icon-input-right-email">
            <i
                :class="iconEyeClass1"
                class="icon-eye-style"
                @click="showPassword1"
            ></i>
        </span>
    </div>

    <div v-if="show_two_password_input" class="input-group mt-3 container-password-secondary">
        <span id="basic-addon1" class="input-group-text icon-input-left">
            <i class="fa-solid fa-lock icon-lock-style"></i>
        </span>
        <input
            id="password"
            :name="name_password_two"
            :placeholder="t(`${placeholderPass2}`)"
            :type="typeInput2"
            autocomplete="current-password"
            class="input-password form-control"
            required
        />

        <span id="basic-addon2" class="input-group-text icon-input-right-email">
            <i
                :class="iconEyeClass2"
                class="icon-eye-style"
                @click="showPassword2"
            ></i>
        </span>
    </div>

</div>

</template>

<script lang="ts" src="./LoginInputView.ts"/>

<style scoped src="./LoginInputView.css"/>
