<template>
    <div>
        <div class="input-group" v-if="type != 'file' && type != 'image'">
            <div class="form-control selector-input">
                <span v-if="!readonly && isForCreation" class="for-creation">
                    <a class="to-add" href="javascript:void(0)" v-on:click="openModalForm(value.data.index)">
                        <i class="fa-solid fa-circle-plus"></i></a>
                </span>
                <div v-if="entity[theKey] != null">
                    <div v-if="entity[theKey].length > 0">
                        <div v-for="option in entity[theKey]" v-if="multiple">
                            <label for="">
                                <i v-if="!readonly" class="fa-solid fa-xmark remove_item"
                                v-on:click="removeItem(option)"></i>
                                {{ option.name }}
                            </label>
                            <input v-model="option.id" :name="theKey + '[]'" type="hidden"/>
                        </div>
                    </div>
                    <!-- esto es cuando hay contenido y es requerido -->
                    <div v-else-if="Object.keys(entity[theKey]).length  > 0">
                        <div v-for="option in entity[theKey]" v-if="multiple">
                            <input v-model="option.id" :name="theKey + '[]'" type="hidden" class="hidden-input"/>
                        </div>
                    </div>
                    <!-- esto es cuando no hay contenido y es requerido -->
                    <div v-else-if="Object.keys(entity[theKey]).length  === 0">
                        <input class="hidden-input" :name="theKey" type="text" :required="required"/>
                    </div>

                        <div v-if="!multiple && !Array.isArray(entity[theKey])">
                            <label for="">
                                <i v-if="!readonly" class="fa-solid fa-xmark remove_item"
                                v-on:click="removeFromSelector(theKey)"></i>
                                <span v-on:click="openModalGeneral(value, theKey)">
                                    {{ entity[theKey].name }}
                                </span>
                            </label>
                            <input v-model="entity[theKey].id" :name="theKey" type="hidden"/>
                        </div>
                </div>

                <!-- Simples requeridos. -->
                <div v-else-if="entity[theKey] === undefined || entity[theKey] === null">
                    <input class="hidden-input" :name="theKey" type="text" :required="required"/>
                </div>

            </div>
            <span class="input-group-text selector-button">
                <a href="javascript:void(0)" v-on:click="openModalGeneral(value, theKey)">
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
            </span>
        </div>
        <div v-else class="file-image-container">
            <div v-if="isEmpty()">
                <i class="fa-solid fa-file file-icon" v-if="type == 'file'"></i>
                <i class="fa-solid fa-image file-icon" v-if="type == 'image'"></i>
            </div>
            <div v-else>
                <div v-if="multiple" class="flexible">
                    <div v-for="option in entity[theKey]">
                        <img v-if="type === 'image'" :alt="option.name" :src="option.url" class="image">
                        <img v-else :alt="option.name" :src="showImageFileOrIcon(option)" class="image">

                        <label for="">
                            <i v-if="!readonly" class="fa-solid fa-xmark remove_item"
                                v-on:click="removeItem(option)"></i>
                            {{ option.name }}
                        </label>
                        <input v-model="option.id" :name="theKey + '[]'" type="hidden" />
                    </div>
                </div>
                <div v-else class="flexible">
                    <img v-if="type === 'image'" :alt="entity[theKey].name" :src="entity[theKey].url" class="image">
                    <img v-else :alt="entity[theKey].name" :src="showImageFileOrIcon(entity[theKey])" class="image">

                    <label for="">
                        <i v-if="!readonly && required" class="fa-solid fa-xmark remove_item"
                            v-on:click="removeFromSelector(theKey)"></i>
                        <strong v-on:click="openModalGeneral(value, theKey)">
                            {{ entity[theKey].name }}
                        </strong>
                    </label>
                    <input v-model="entity[theKey].id" :name="theKey" type="hidden" />
                </div>
            </div>
            <button v-if="!readonly" class="btn-img-file" type="button" v-on:click="openModalGeneral(value, theKey)">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> {{ $t('Select') }} {{ $t(type) }}
            </button>
        </div>


        <div>
            <div :id="modal_id" :aria-labelledby="modal_id + 'Label'" aria-hidden="true" class="modal fade"
                data-bs-theKeyboard="false" tabindex="-1">
                <div v-if="modal_open" class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="title-modal">{{ $t('Select from these options') }}</h3>
                            <button aria-label="Close" class="button-close-modal-show-file" data-bs-dismiss="modal"
                                type="button" v-on:click="closeModal">
                                <i class="fa-regular fa-circle-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">

                            <list-component v-if="type === 'object'" :csrf="csrf" :fields="current.data.fields"
                                :itemsSelected="entity[theKey]" :multiple="multiple" :name_choose="current.name"
                                :object="current.data.values" :permissions="[]" :req="required"
                                :url="getDependences(current.data.url)" @onSelected="markSelected" />


                            <media-file-component v-if="type === 'image' || type == 'file'" :accept="accept"
                                :csrf="csrf" :field_id="field_id" :field_layout="field_layout"
                                :isImage="type === 'image'" :itemsSelected="entity[theKey]" :multiple="value.multiple"
                                :post="value.data.store" :quantity="paginate" :selectable="true"
                                :setChosen="markSelected" :sorts="value.data.sorts" :url="value.data.url"
                                :isObjectSelector="true" />

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
        <div>
            <div :id="modal_form_id" aria-hidden="true" aria-labelledby="formEditModalLabel" class="modal fade"
                data-bs-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-xl" style="min-width: 80vw;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"
                                v-on:click="closeModalForm"></button>
                        </div>
                        <div class="modal-body">
                            <div style="max-width: 1200px; margin:0 auto">
                                <InterfaceLoaderComponent v-if="dataModal.loading" />
                                <!-- <form-component :http_method="method" v-if="object_ != null && !loading" :object="object_"
                                    :fields="fields" :url="url" :icons="icons" :csrf="csrf" :title="title" :custom_fields="custom_fields"/> -->
                                <form-component v-if="!dataModal.loading" :csrf="csrf"
                                    :custom_fields="dataModal.custom_fields" :fields="value.data.fields"
                                    :icons="dataModal.icons" :itemsSelected="entity[theKey]" :object="dataModal.values"
                                    :title="$t('Register new') + ' ' + t(dataModal.title.toLowerCase(), 1)"
                                    :url="dataModal.create" http_method="POST" @onSelected="markCreated" />
                                <div v-if="error != null">
                                    <strong>{{ error }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:void(0)" v-on:click="closeModalForm"><strong>{{ t('Cancel')
                                    }}</strong></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./ObjectSelectorComponent.ts" />
<style scoped src="./ObjectSelectorComponent.css" />
