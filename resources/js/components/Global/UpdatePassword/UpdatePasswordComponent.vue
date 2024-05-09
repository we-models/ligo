<template>
  <div class="container-update-password">
    <alert-component />

    <h4 class="crud-title">{{ $t('Update') + ' ' + $t('my password') }}</h4>
    <form :action="url" method="POST" @submit.prevent="save" class="form-update-password">
      <input type="hidden" name="token" :value="csrf">

      <div class="error-msg" v-if="errorMsg">
        <div class="icon-alert">
          <i class="fa-solid fa-triangle-exclamation icon-warning-style"></i>
        </div>
        <span>
          <strong class="msg-error">{{ errorMsg }}</strong>
        </span>
      </div>


      <login-input-view show_two_password_input="true" name_password_two="password_confirmation"
        placeholder_password_one="write_your_current_password" placeholder_password_two="confirm_new_password"
        :old_value_email="email" :styles="true"></login-input-view>


      <div class="row">
        <div class="btn-submit-content">
          <button class="btn btn-submit" type="submit">{{ $t('Update') + " " + $t('my password') }}</button>
        </div>
      </div>


    </form>
  </div>
</template>

<script lang="ts" src="./UpdatePasswordComponent.ts" />

<style scoped src="./UpdatePasswordComponent.css" />
