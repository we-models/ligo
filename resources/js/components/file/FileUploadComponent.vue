<template>
    <div class="panel-body" style="padding:1em">
        <div class="upload-drop-zone" id="drop-zone" style="background-color: #f2f2f2">
            <input type="file" name="file" :multiple="this.multiple" id="file_content" v-on:change="show_the_file_preview"
                   class="inputfile" :accept="this.accept == null || this.accept.length < 1 ? Cts.file_types : this.accept " />
            <label for="file_content" style="height: 100%; width: 100%; padding: 15px">
                <div style="width: 100%; text-align: center">
                    <p id="image_label">{{ $t('Add new file') }}</p>
                </div>
            </label>
        </div>
        <div v-if="the_files.length > 0">
            <ul class="nav justify-content-end">
                <li class="nav-item">
                    <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                       v-on:click="uploadTheFile([the_file_active])">
                        <i class="fa-solid fa-upload"></i>
                        {{ $t('Upload') }}
                    </a>
                </li>
                <li class="nav-item" v-if="the_files.length > 1">
                    <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                       v-on:click="uploadTheFile(the_files)">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        {{ $t('Upload all') }}
                    </a>
                </li>
            </ul>
            <div class="progress" v-if="uploading">
                <div id="the_file_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar" aria-label="Animated striped example" :aria-valuenow="percent" aria-valuemin="0"
                     aria-valuemax="100"></div>
            </div>

            <div class="d-flex align-items-start file_data_container">
                <div class="nav flex-column nav-pills me-4" id="v-pills-tab" role="tablist" aria-orientation="vertical" v-if="!is_mobile">
                    <div style="max-height: 70vh; overflow-y: scroll; width: 250px;" >
                        <table class="table file-table">
                            <tr v-for="item in this.the_files">
                                <td class="remove" style="width: 20%">
                                    <a href="javascript:void(0)" class="btn_delete" v-on:click="removeTheFile(item)">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                                <td :id="'v-pills-' + item.uuid + '-tab'" style="width: 80%" data-bs-toggle="pill"
                                    :data-bs-target="'#v-pills-' + item.uuid" type="button" role="tab" href="#"
                                    :class="item.uuid === the_file_active.uuid ? 'active-file' : ''"
                                    :aria-controls="'v-pills-' + item.uuid"
                                    :aria-selected="item.uuid === the_file_active.uuid"
                                    v-on:click="changeActiveTheFile(item)">
                                    <p style="text-overflow: ellipsis">{{ item.title }}</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="tab-content" id="v-pills-tabContent" style="border:none">
                    <div style="padding: 10px" v-if="is_mobile && this.the_file_active != null">
                        <div class="dropdown" style="text-align: center">
                            <button class="btn btn-primary dropdown-toggle" style="width: 100%" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                {{ this.the_file_active.title }}
                            </button>
                            <ul class="dropdown-menu" v-if="this.the_files.length > 0">
                                <li v-for="item in this.the_files">
                                    <a class="dropdown-item" href="#"
                                       v-on:click="changeActiveTheFile(item)">{{ item.title }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-for="item in this.the_files"
                         :class="'tab-pane fade ' + (item.uuid === this.the_file_active.uuid ? 'show active' : '')"
                         :id="'v-pills-' + item.uuid" role="tabpanel" aria-labelledby="'v-pills-' + item.uuid +'-tab'"
                         tabindex="0">
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="card default-file">

                                    <object :id="'obj_' + item.uuid" style="width: 100%; min-height:300px"></object>

                                    <div class="alert alert-danger" role="alert" v-if="item.error != null">
                                        {{ item.error }}
                                        <p>
                                            <a href="javascript:void(0)"
                                               v-on:click="uploadTheFile([item])">{{ $t('Retry?') }}</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div v-if="this.the_file_active.uuid === item.uuid">
                                    <table class="table table-bordered table-detail">
                                        <tr>
                                            <td> {{ $t('Name') }} </td>
                                            <td>
                                                <input :disabled="uploading" type="text" name="name"
                                                       v-model="item.title" class="input_hd" minlength="1" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Size') }}</td>
                                            <td>{{ (item.size / 1024).toFixed(2) + ' KB' }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Type') }}</td>
                                            <td style="display: grid">
                                                <p style="overflow: hidden; text-overflow: ellipsis;">
                                                    {{ item.type }}
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Visibility') }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <select name="" v-model="item.visibility" id=""
                                                            class="form-control">
                                                        <option :selected="item.visibility === 'public'"
                                                                value="public">{{ $t('Public') }}</option>
                                                        <option :selected="item.visibility === 'business'"
                                                                value="business">{{ $t('For Business') }}</option>
                                                        <option :selected="item.visibility === 'private'"
                                                                value="private">{{ $t('Private') }}</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { v4 as uuidv4 } from "uuid";
import cts from '../Constants';
export default {
    props: ['post', 'csrf', 'multiple', 'is_mobile', 'accept',  'field_id', 'field_layout'],
    name: "FileUploadComponent",
    data() {
        return {
            Cts : cts,
            the_files: [],
            the_file_active: null,
            uploading: false,
            progress: 0,
            percent: 0,
            totalSize: 0,
        }
    },
    methods: {
        show_the_file_preview: function (input) {
            input = input.target;
            if (input.files) {
                input = Array.from(input.files);
                input.forEach((file) => {
                    let reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = this.forceOnLoad;
                    file['title'] = file.name;
                    file['reader'] = reader;
                    file['uuid'] = uuidv4().toString();
                    file['visibility'] = 'public';
                    file['error'] = null;
                });
                this.the_files = this.the_files.concat(input);
            }
            if (this.the_files.length > 0) this.the_file_active = this.the_files[0];
        },
        uploadTheFile: function (list) {
            this.uploading = true;
            this.resetLoader();
            for (let i = 0; i < list.length; i++) {
                this.totalSize += Number(list[i].size);
            }
            for (let i = 0; i < list.length; i++) {
                this.startUploading(list[i]);
            }
        },
        removeTheFile: function (item) {
            this.the_files = this.the_files.filter(the_file => the_file.uuid !== item.uuid);
            if (this.the_file_active.uuid === item.uuid && this.the_files.length > 0) this.the_file_active = this.the_files[0];
        },
        changeActiveTheFile: function (item) {
            this.the_file_active = item;
        },
        resetLoader: function () {
            this.progress = 0;
            this.percent = 0;
            this.totalSize = 0;
        },
        forceOnLoad: function () {
            for (let i = 0; i < this.the_files.length; i++) {
                let element = this.$el.querySelector('#obj_' + this.the_files[i].uuid);
                if (element == null) return;
                element.setAttribute('data', this.the_files[i].reader.result);
            }
        },
        startUploading: function (item) {
            let formData = new FormData();
            formData.append("name", item.title);
            formData.append("visibility", item.visibility);
            formData.append("the_file", item);
            jQuery.ajax(`${this.post}?field=${this.field_id}&layout=${this.field_layout}`, {
                method: 'POST',
                data: formData,
                headers: { 'X-CSRF-TOKEN': this.csrf },
                processData: false,
                contentType: false,
                xhr: () => {
                    let xhr = new XMLHttpRequest();
                    let currentLoaded = 0;
                    xhr.upload.onprogress = (e) => {
                        currentLoaded += (e.loaded - currentLoaded);
                        this.progress += currentLoaded;
                        this.percent = (this.progress / this.totalSize) * 100;
                        this.$el.querySelector('#the_file_progress_bar').style.width = this.percent.toFixed(2) + '%';
                    };
                    return xhr;
                },
                success: (_success) => {
                    this.removeTheFile(item)
                },
                error: (error) => {
                    this.the_files = this.the_files.map((item) => {
                        if (item.uuid === item.uuid) item.error = error.responseText.toString();
                        return item;
                    });
                    this.resetLoader();
                },
                complete: () => {
                    if (this.percent >= 100 && this.the_files.length === 0) {
                        this.resetLoader();
                        this.uploading = false;
                    }
                }
            });
        },
    }
}
</script>

<style scoped>
.default-file{
    background-color: #f2f2f2;
    padding:1em;
    background-image: url('/images/default.jpg');
    background-position: center center;
    background-size: contain;
}
</style>
