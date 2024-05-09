<template>
    <div>
        <div v-cloak class="vue-layout">
            <ul class="nav nav-pills all_tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button id="create-tab" aria-controls="create-tab-pane" aria-selected="true" class="nav-link active"
                        data-bs-target="#create-tab-pane" data-bs-toggle="tab" role="tab" type="button"
                        v-on:click="setSelected('.update.profile')">
                        <span class="tab-title"> {{ $t('Update') }}
                            {{ $t(title.toLowerCase()) }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button id="list-tab" aria-controls="list-tab-pane" aria-selected="false" class="nav-link"
                        data-bs-target="#list-tab-pane" data-bs-toggle="tab" role="tab" type="button"
                        v-on:click="setSelected('.change.password')">
                        <span>{{ t('Update') + " " + t('my password') }}</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div id="create-tab-pane" aria-labelledby="create-tab" class="tab-pane fade show active" tabindex="0">
                    <div class="row">
                        <form-component :csrf="csrf" :custom_fields="[]" :fields="JSON.parse(fields)" :icons="icons"
                            :title="t(title.toLowerCase(), 1)" :url="url_update_profile" :prefix="t('Update')" http_method="POST"
                            :object="JSON.parse(object)" />

                    </div>
                </div>

                <div id="list-tab-pane" aria-labelledby="list-tab" class="tab-pane fade form-div" role="tabpanel"
                    tabindex="0">

                    <update-password-component :email="JSON.parse(object)" :url="url_update_password" http_method="POST" :csrf="csrf"/>
                </div>
            </div>
        </div>
    </div>

    <!-- <InterfaceLoaderComponent v-if="!loaded"/> -->


    <!-- <form-component
        :csrf="csrf"
        :custom_fields="[]"
        :fields="JSON.parse(fields)"
        :icons="icons"
        :title="t(title.toLowerCase(), 1)"
        :url="create"
        :prefix="t('Register new')"
        http_method="POST"
        :object="JSON.parse(object)"
    /> -->
</template>

<script lang="ts" src="./ProfileView.ts" />

<style scoped src="./ProfileView.css" />
