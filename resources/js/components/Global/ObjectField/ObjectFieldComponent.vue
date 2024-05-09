<template>
    <div>
        <div class="input-group" v-on:click="openModalGeneral(value, theKey)">
            <div class="form-control" style="height: auto">
                <div v-if="entity[theKey] != null">
                    <div
                        v-for="option in entity[theKey]"
                        v-if="Array.isArray(entity[theKey])"
                    >
                        <label for=""
                        ><i
                            v-if="!readonly"
                            class="fa-solid fa-xmark remove_item"
                            v-on:click="removeItem(option)"
                        ></i
                        >{{ option.name }}</label
                        >
                        <input
                            v-model="option.id"
                            :name="theKey + '[]'"
                            type="hidden"
                        />
                    </div>
                    <div v-if="!Array.isArray(entity[theKey])">
                        <label for="">{{ entity[theKey].name }}</label>
                        <input
                            v-model="entity[theKey].id"
                            :name="theKey"
                            type="hidden"
                        />
                    </div>
                </div>
            </div>
            <span v-if="!readonly" class="input-group-text">
                <a
                    href="javascript:void(0)"
                    style="width: 100%"
                    v-on:click="openModal(value, theKey)"
                >{{ $t("Select") }}</a
                >
            </span>
        </div>

        <div>
            <div
                :id="modal_id"
                :aria-labelledby="modal_id + 'Label'"
                aria-hidden="true"
                class="modal fade"
                data-bs-theKeyboard="false"
                tabindex="-1"
            >
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>{{ $t("Select from these options") }}</h3>
                            <button
                                aria-label="Close"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                type="button"
                                v-on:click="closeModal"
                            ></button>
                        </div>
                        <div class="modal-body">

                            <InterfaceLoaderComponent v-if="loading"/>

                            <list-component
                                v-if="modalOpened"
                                :csrf="csrf"
                                :fields="modal_fields"
                                :itemsSelected="entity[modal_current]"
                                :multiple="multiple"
                                :name_choose="modal_choose_name"
                                :object="modal_object"
                                :permissions="[]"
                                :req="required"
                                :url="modal_url"
                                @onSelected="markSelected"
                            />

                            <div v-if="error != null">
                                <strong>{{ error }}</strong>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:void(0)" v-on:click="closeModal"
                            ><strong>{{ $t("Cancel") }}</strong></a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./ObjectFieldComponent.ts"/>
<style scoped src="./ObjectFieldComponent.css"/>
