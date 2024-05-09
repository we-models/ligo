<template>
    <div>
        <div class="modal fade" id="formEditModal" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="formEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div style="max-width: 1200px; margin:0 auto">
                            <div class="interface_loader" v-if="loading"></div>
                            <form-component :http_method="method" v-if="object_ != null && !loading" :object="object_"
                                :fields="fields" :url="url" :icons="icons" :csrf="csrf" :title="title" :custom_fields="custom_fields"/>
                            <div v-if="error != null">
                                <strong>{{ error }}</strong>
                            </div>
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

<script>

import { trans } from 'laravel-vue-i18n';

export default {
    name: "ModalFormComponent",
    data() {
        return {
            object_: null,
            fields: null,
            url: '',
            icons: [],
            csrf: '',
            title: '',
            loading: false,
            error: null,
            method: null,
            custom_fields : []
        }
    },
    mounted() {
        this.emitter.on("fillModalCRUD", params => {
            jQuery('#formEditModal').show();
            let uri_separator = params.index.split('?')
            params.index = uri_separator[0];
            let uri = params.index + '/' + params.id + (params.method === 'PUT' ? '/edit' : '');
            if(uri_separator.length > 1){
                uri+= `?${uri_separator[1]}` ;
            }
            this.method = params.method;
            this.loading = true;
            this.error = null;
            console.log(uri);
            fetch(uri).then(response => response.json()).then((data) => {
                this.object_ = data.object;
                if(this.object_ == null){
                    this.error = trans('This element doesnt have some fields');
                }else{
                    this.fields = data.fields;
                    this.url = data.url;
                    this.csrf = data.csrf;
                    this.icons = data.icons;
                    this.title = data.title;
                    this.custom_fields = data.custom_fields !== undefined? data.custom_fields : [] ;
                }
            }).catch((e) => {
                this.error = trans('Can not get the data for this element');
                this.object_ = null;
                this.fields = null;
            }).finally(() => {
                this.loading = false;
            });
        });
    },
    methods: {
        closeModal: function () {
            jQuery('#formEditModal').hide();
            this.object_ = null;
        }
    }
}
</script>

<style scoped>

</style>
