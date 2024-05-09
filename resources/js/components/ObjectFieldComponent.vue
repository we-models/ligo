<template>
    <div>

        <div class="input-group" v-on:click="openModalGeneral(value, theKey)">
            <div class="form-control" style="height: auto">
                <div v-if="entity[theKey] != null">
                    <div v-for="option in entity[theKey]" v-if="Array.isArray(entity[theKey])">
                        <label for=""><i v-if="!readonly" class="fa-solid fa-xmark remove_item"
                                v-on:click="removeItem(option)"></i>{{ option.name }}</label>
                        <input type="hidden" :name="theKey + '[]'" v-model="option.id" />
                    </div>
                    <div v-if="!Array.isArray(entity[theKey])">
                        <label for="">{{ entity[theKey].name }}</label>
                        <input type="hidden" :name="theKey" v-model="entity[theKey].id" />
                    </div>
                </div>
            </div>
            <span class="input-group-text" v-if="!readonly">
                <a href="javascript:void(0)" v-on:click="openModal(value, theKey)"
                    style="width: 100%">{{ $t('Select') }}</a>
            </span>
        </div>

        <div>
            <div class="modal fade" :id="modal_id" data-bs-theKeyboard="false" tabindex="-1"
                :aria-labelledby="modal_id + 'Label'" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>{{ $t('Select from these options') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="interface_loader" v-if="loading"></div>

                            <list-component
                                v-if="modalOpened"
                                :object="modal_object"
                                :fields="modal_fields"
                                :url="modal_url"
                                :csrf="csrf"
                                :permissions="[]"
                                :multiple="this.multiple"
                                :name_choose="modal_choose_name"
                                @onSelected="markSelected"
                                :itemsSelected="this.entity[this.modal_current]"
                                :req="required" />

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
    </div>
</template>

<script>

import { v4 as uuidv4 } from 'uuid';

export default {
    name: "ObjectFieldComponent",
    props: ['ent', 'theKey', 'value', 'csrf', 'required', 'readonly', 'multiple', 'paginate'],
    data() {
        return {
            modalOpened: false,
            modal_object: null,
            modal_fields: null,
            modal_url: null,
            modal_current: null,
            modal_id: uuidv4().toString(),
            entity: this.ent,
            modal_choose_name : ''
        }
    },
    methods: {
        closeModal: function () {
            jQuery('#' + this.modal_id).hide();
            this.modalOpened = false;
            this.modal_object = null;
            this.modal_fields = null;
            this.modal_url = null;
            this.modal_current = null;
        },
        openModalGeneral: function (value, theKey) {
            if (!this.multiple) this.openModal(value, theKey);
        }
        ,
        openModal: function (value, theKey) {
            jQuery('#' + this.modal_id).show();
            this.modal_object = value.data.values;
            this.modal_fields = value.data.fields;
            this.modal_choose_name = value.name;
            this.modal_url = value.data.url;
            this.modalOpened = true;
            this.modal_current = theKey;
        },
        markSelected: function (selected) {
            this.closeModal();
            selected = !this.multiple ? selected[0] : selected;
            this.entity[this.theKey] = selected;
            this.$emit('onSetSelectedItems', this.theKey, selected);
        },
        removeItem: function (op) {
            this.entity[this.theKey] = this.entity[this.theKey].filter(item => item.id !== op.id);
            this.$emit('onSetSelectedItems', this.theKey, this.entity[this.theKey]);
        }
    }

}
</script>

<style scoped>
.modal {
    height: 100vh;
    width: 100%;

}

.modal.fade .modal-dialog {
    transform: unset !important;
}
</style>
