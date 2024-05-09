<template>
    <div>
        <div class="input-group">
            <div class="form-control" style="height: auto">
                <input :name="'$$lat_' + name" type="hidden">
                <input :name="'$$long_' + name" type="hidden">
                <label for="">{{ this.entity }}</label>
            </div>
            <span v-if="!readonly" class="input-group-text">
                <a href="javascript:void(0)" style="width: 100%" v-on:click="openModalMap">{{ $t('Select') }}</a>
            </span>
        </div>
        <div aria-hidden="true" class="modal fade modal_map" data-bs-theKeyboard="false" role="dialog" tabindex="-1">
            <div v-if="show_map" class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLongTitle" class="modal-title">{{ $t('Select location') }}</h5>
                        <button class="close" type="button" v-on:click="closeModalMap">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input v-model="this.search" class="form-control pac-input" type="search">
                        </div>
                        <div class="map">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" v-on:click="closeModalMap">{{ $t('Close') }}
                        </button>
                        <button class="btn btn-primary" type="button" v-on:click="saveChanges">
                            {{ $t('Mark as selected') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script lang="ts" src="./MapComponent.ts"/>

<style scoped src="./MapComponent.css"/>
