<template>
    <div>
        <div id="formEditModal" aria-hidden="true" aria-labelledby="formEditModalLabel" class="modal fade"
             data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"
                                v-on:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <InterfaceLoaderComponent v-if="loading"/>
                        <form-component v-if="object_ != null && !loading" :csrf="csrf"
                                        :custom_fields="custom_fields"
                                        :prefix ="prefix"
                                        :fields="fields" :http_method="method" :icons="icons" :object="object_"
                                        :title="title"
                                        :url="url"/>
                        <div v-if="error != null">
                            <strong>{{ error }}</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0)" v-on:click="closeModal"><strong>{{ $t('Cancel') }}</strong></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script lang="ts" src="./ModalFormComponent.ts"/>

<style scoped src="./ModalFormComponent.css"/>
