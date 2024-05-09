<template>
    <div class="panel-body p-3">
        <div id="drop-zone" class="upload-drop-zone">
            <input v-if="!isImage" id="file_content"
                :accept="this.accept == null || this.accept.length < 1 ? Cts.file_types : this.accept"
                :multiple="this.multiple" class="inputfile" name="file" type="file"
                v-on:change="show_media_file_preview" />

            <input v-else id="image_content" :multiple="this.multiple" accept=".jpg, .jpeg, .png, .webp, .gif"
                class="inputfile" name="file" type="file" v-on:change="show_media_file_preview" />

            <div class="text-info">
                <p>Arrastra archivos a cualquier lugar para subirlos</p>
            </div>

            <label :for="isImage ? 'image_content' : 'file_content'" class="label-load-media-file">
                <div>
                    <p id="image_label">{{ isImage ? $t('Add new image') : $t('Add new file') }}</p>
                </div>
            </label>
        </div>
        <div v-if="media_files.length > 0">
            <ul class="nav justify-content-end">
            </ul>
            <div v-if="uploading" class="progress">
                <div :id="isImage ? 'image_progress_bar' : 'the_file_progress_bar'" :aria-valuenow="percent"
                    aria-label="Animated striped example" aria-valuemax="100" aria-valuemin="0"
                    class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
            </div>

            <div :class="isImage ? 'image_data_container' : 'file_data_container'" class="d-flex align-items-start">
                <div v-if="!is_mobile" id="v-pills-tab"  aria-orientation="vertical"
                    class="nav flex-column nav-pills" role="tablist">
                    <div class="delete-media-file-section">
                        <table class="table-remove">
                            <tr v-for="item in media_files" :id="'tr' + item.uuid + '-tab'">
                                <td class="remove">
                                    <a class="btn_delete" href="javascript:void(0)"
                                        v-on:click="removeTheMediaFile(item)">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                                <td :id="'v-pills-' + item.uuid + '-tab'" :aria-controls="'v-pills-' + item.uuid"
                                    :aria-selected="item.uuid === media_file_active.uuid"
                                    :class="item.uuid === media_file_active.uuid ? 'active-image' : ''"
                                    :data-bs-target="'#v-pills-' + item.uuid" class="td-text" data-bs-toggle="pill"
                                    href="#" role="tab" type="button" v-on:click="changeActiveMediaFile(item)">
                                    <p class="title-table-remove">{{ item.title }}</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div id="v-pills-tabContent" class="tab-content">
                    <div v-if="is_mobile && this.media_file_active != null" class="p-3">
                        <div class="dropdown text-center button-choose-mobile">
                            <button aria-expanded="false" class="btn btn-primary dropdown-toggle button-show-file-mobile"
                                data-bs-toggle="dropdown" type="button">
                                {{ this.media_file_active.title }}
                            </button>
                            <ul v-if="this.media_files.length > 0" class="dropdown-menu">
                                <li v-for="item in this.media_files" class="drop-list">
                                    <a class="dropdown-item link-style-text" href="#"
                                        v-on:click="changeActiveMediaFile(item)">
                                        {{ item.title }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-for="item in this.media_files" :id="'v-pills-' + item.uuid"
                        :class="'tab-pane fade ' + (item.uuid === this.media_file_active.uuid ? 'show active' : '')"
                        aria-labelledby="'v-pills-' + item.uuid +'-tab'" role="tabpanel" tabindex="0">
                        <div class="row">
                            <div :class="isImage ? 'col-lg-3' : 'col-lg-5'">
                                <div class="card default-file">

                                    <img v-if="isImage" :id="'img_' + item.uuid" :alt="item.title" class="img_selector">
                                    <object v-else :id="'obj_' + item.uuid" class="object-media-file"></object>

                                    <div v-if="item.error != null" class="alert alert-danger" role="alert">
                                        {{ item.error }}
                                        <p>
                                            <a href="javascript:void(0)" v-on:click="uploadTheMediaFile([item])">
                                                {{ $t('Retry?') }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div :class="isImage ? 'col-lg-9' : 'col-lg-7'" class="table-info-file">
                                <div v-if="this.media_file_active.uuid === item.uuid">
                                    <table class="table table-bordered image-table">
                                        <tr>
                                            <td> {{ $t('Name') }}</td>
                                            <td>
                                                <!-- <input v-model="item.title" :disabled="uploading" class="input_hd"
                                                    minlength="1" name="name" required type="text"> -->
                                                {{ item.title }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Size') }}</td>
                                            <td>{{ (item.size / 1024).toFixed(2) + ' KB' }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Type') }}</td>
                                            <td>
                                                {{ item.type }}
                                            </td>
                                        </tr>
                                        <tr v-if="isImage">
                                            <td>{{ $t('Height') }}</td>
                                            <td>{{ item.height }}px</td>
                                        </tr>
                                        <tr v-if="isImage">
                                            <td>{{ $t('Width') }}</td>
                                            <td>{{ item.width }}px</td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="button-mediafile-upload">
                            <div v-if="media_files.length === 1" class="nav-item">
                                <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                                    v-on:click="uploadTheMediaFile([media_file_active])">
                                    <i v-if="!isObjectSelector" class="fa-solid fa-upload"></i>
                                    {{ isObjectSelector ? $t('Select') : $t('Upload') }}
                                </a>
                            </div>
                            <div v-if="media_files.length > 1" class="nav-item">
                                <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                                    v-on:click="uploadTheMediaFile(media_files)">
                                    <i v-if="!isObjectSelector" class="fa-solid fa-cloud-arrow-up"></i>
                                    {{ isObjectSelector ? $t('Select all') : $t('Upload all') }}
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>
</template>

<script lang="ts" src="./MediaFileUploadComponent.ts" />

<style scoped src="./MediaFileUploadComponent.css" />
