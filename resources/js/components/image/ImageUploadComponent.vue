<template>
    <div class="panel-body" style="padding:1em">
        <div class="upload-drop-zone" id="drop-zone" style="background-color: #f2f2f2">
            <input type="file" name="file" :multiple="this.multiple" id="image_content" v-on:change="show_image_preview"
                class="inputfile" accept=".jpg, .jpeg, .png, .webp, .gif" />
            <label for="image_content" style="height: 100%; width: 100%; padding: 15px">
                <div style="width: 100%; text-align: center">
                    <p id="image_label">{{ $t('Add new image') }}</p>
                </div>
            </label>
        </div>
        <div v-if="images.length > 0">
            <ul class="nav justify-content-end">
                <li class="nav-item">
                    <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                        v-on:click="uploadImages([image_active])">
                        <i class="fa-solid fa-upload"></i>
                        {{ $t('Upload') }}
                    </a>
                </li>
                <li class="nav-item" v-if="images.length > 1">
                    <a :class="'nav-link ' + (uploading ? 'disabled' : '')" href="javascript:void(0)"
                        v-on:click="uploadImages(images)">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        {{ $t('Upload all') }}
                    </a>
                </li>
            </ul>
            <div class="progress" v-if="uploading">
                <div id="image_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" aria-label="Animated striped example" :aria-valuenow="percent" aria-valuemin="0"
                    aria-valuemax="100"></div>
            </div>

            <div class="d-flex align-items-start image_data_container">
                <div class="nav flex-column nav-pills me-4" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <div style="max-height: 70vh; overflow-y: scroll; width: 250px;" v-if="!is_mobile">
                        <table class="table image-table">
                            <tr v-for="item in images">
                                <td class="remove" style="width: 20%">
                                    <a href="javascript:void(0)" class="btn_delete" v-on:click="removeImage(item)">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                                <td :id="'v-pills-' + item.uuid + '-tab'" style="width: 80%" data-bs-toggle="pill"
                                    :data-bs-target="'#v-pills-' + item.uuid" type="button" role="tab" href="#"
                                    :class="item.uuid === image_active.uuid ? 'active-image' : ''"
                                    :aria-controls="'v-pills-' + item.uuid"
                                    :aria-selected="item.uuid === image_active.uuid"
                                    v-on:click="changeActiveImage(item)">
                                    <p style="text-overflow: ellipsis">{{ item.title }}</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="tab-content" id="v-pills-tabContent" style="border:none">
                    <div style="padding: 10px" v-if="is_mobile && image_active != null">
                        <div class="dropdown" style="text-align: center">
                            <button class="btn btn-primary dropdown-toggle" style="width: 100%" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ image_active.title }}
                            </button>
                            <ul class="dropdown-menu" v-if="images.length > 0">
                                <li v-for="item in images">
                                    <a class="dropdown-item" href="#"
                                        v-on:click="changeActiveImage(item)">{{ item.title }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-for="item in images"
                        :class="'tab-pane fade ' + (item.uuid === image_active.uuid ? 'show active' : '')"
                        :id="'v-pills-' + item.uuid" role="tabpanel" aria-labelledby="'v-pills-' + item.uuid +'-tab'"
                        tabindex="0">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="card" style=" background-color: #f2f2f2; padding:1em; ">

                                    <img class="img_selector" :alt="item.title" :id="'img_' + item.uuid">

                                    <div class="alert alert-danger" role="alert" v-if="item.error != null">
                                        {{ item.error }}
                                        <p>
                                            <a href="javascript:void(0)"
                                                v-on:click="uploadImages([item])">{{ $t('Retry?') }}</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div v-if="image_active.uuid === item.uuid">
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
                                            <td>{{ item.type }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Height') }}</td>
                                            <td><input :disabled="uploading" class="number_hd" type="number" min="1"
                                                    step="1" v-model="item.height"
                                                    :max="item.height === 0 ? 768 : (item.height * 3)"> px</td>
                                        </tr>
                                        <tr>
                                            <td>{{ $t('Width') }}</td>
                                            <td><input :disabled="uploading" class="number_hd" type="number" min="1"
                                                    step="1" v-model="item.width"
                                                    :max="item.width === 0 ? 768 : (item.width * 3)"> px</td>
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

export default {
    props: ['post', 'csrf', 'multiple', 'is_mobile'],
    name: "ImageUploadComponent",
    data() {
        return {
            images: [],
            image_active: null,
            uploading: false,
            progress: 0,
            percent: 0,
            totalSize: 0,
        }
    },
    methods: {
        show_image_preview: function (input) {
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
                this.images = this.images.concat(input);
            }
            if (this.images.length > 0) this.image_active = this.images[0];
        },
        uploadImages: function (list) {
            this.uploading = true;
            this.resetLoader();
            for (let i = 0; i < list.length; i++) {
                this.totalSize += Number(list[i].size);
            }
            for (let i = 0; i < list.length; i++) {
                this.startUploading(list[i]);
            }
        },
        removeImage: function (item) {
            this.images = this.images.filter(image => image.uuid !== item.uuid);
            if (this.image_active.uuid === item.uuid && this.images.length > 0) this.image_active = this.images[0];
        },
        changeActiveImage: function (item) {
            this.image_active = item;
        },
        resetLoader: function () {
            this.progress = 0;
            this.percent = 0;
            this.totalSize = 0;
        },
        forceOnLoad: function () {
            for (let i = 0; i < this.images.length; i++) {
                let element = this.$el.querySelector('#img_' + this.images[i].uuid);
                if (element == null) return;
                element.setAttribute('src', this.images[i].reader.result);
                element.onload = () => {
                    this.$forceUpdate();
                    this.images[i].width = element.naturalWidth;
                    this.images[i].height = element.naturalHeight;
                }
            }
        },
        startUploading: function (item) {
            let formData = new FormData();
            formData.append("name", item.title);
            formData.append("height", item.height);
            formData.append("width", item.width);
            formData.append("visibility", item.visibility);
            formData.append("image", item);
            jQuery.ajax(this.post, {
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
                        this.$el.querySelector('#image_progress_bar').style.width = this.percent.toFixed(2) + '%';
                    };
                    return xhr;
                },
                success: (_success) => {
                    this.removeImage(item)
                },
                error: (error) => {
                    this.images = this.images.map((item) => {
                        if (item.uuid === item.uuid) item.error = error.responseText.toString();
                        return item;
                    });
                    this.resetLoader();
                },
                complete: () => {
                    if (this.percent >= 100 && this.images.length === 0) {
                        this.resetLoader();
                        this.uploading = false;
                    }
                }
            });
        },
    }
}
</script>
