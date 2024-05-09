<template>
    <div>
        <ul id="mediaFile" class="nav nav-tabs nav-pills" role="tablist">
            <li class="nav-item" role="presentation">
                <button id="upload-image" aria-controls="upload-image-pane" aria-selected="true" class="nav-link active"
                    data-bs-target="#upload-image-pane" data-bs-toggle="tab" role="tab" type="button"
                    v-on:click="toggleMediaList()">
                    <span>{{
                        isImage ? $t("Upload images") : $t("Upload files")
                    }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button id="image-list" aria-controls="image-list-pane" aria-selected="false" class="nav-link"
                    data-bs-target="#image-list-pane" data-bs-toggle="tab" role="tab" type="button"
                    v-on:click="toggleMediaList()">
                    <span >{{
                        isImage ? $t("List of images") : $t("List of files")
                    }}</span>
                </button>
            </li>
        </ul>

        <div id="mediaFileContent" class="tab-content">
            <div id="upload-image-pane" aria-labelledby="upload-image" class="tab-pane fade show active" role="tabpanel"
                tabindex="0">
                <media-file-upload-component v-if="!this.showList" :accept="this.accept" :csrf="this.csrf"
                    :field_id="this.field_id" :field_layout="this.field_layout" :isImage="isImage"
                    :is_mobile="this.is_mobile" :multiple="this.multiple" :post="this.post" @onSelected="this.setChosen"
                    :isObjectSelector="this.isObjectSelector" />
            </div>
            <div id="image-list-pane" aria-labelledby="image-list" class="tab-pane fade" role="tabpanel" tabindex="0">
                <div>
                    <media-file-list-component v-if="this.showList" :isImage="isImage" :is_mobile="this.is_mobile"
                        :itemsSelected="this.itemsSelected" :multiple="this.multiple"
                        :quantity="!this.is_mobile ? this.quantity : 12" :selectable="this.selectable"
                        :sorts="this.sorts" :url="this.url" @onSelected="this.setChosen"
                        @onShowDetails="this.showMediaModal" />
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div id="mediaModal" aria-hidden="true" aria-labelledby="formEditModalLabel" class="modal fade"
            data-bs-keyboard="false" tabindex="-1">
            <div v-if="this.showed !== null" class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title title-modal">
                            {{ this.showed.name }}
                        </h1>
                        <button aria-label="Close" class="button-close-modal-show-file" data-bs-dismiss="modal"
                            type="button" v-on:click="closeModal">
                            <i class="fa-regular fa-circle-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div v-if="!isImage" class="card img-card-container">
                                    <embed v-if="!is_microsoft_file" :src="this.showed.url" :type="this.showed.mimetype"
                                        class="object-enb" />
                                    <iframe v-if="is_microsoft_file" :src="this.microsoft + this.showed.url"></iframe>
                                </div>
                                <div v-else class="card img-card-container">
                                    <img :alt="this.showed.name" :src="this.showed.url" class="img_modal" />
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <table class="table table-bordered table-info-file">
                                    <tr>
                                        <th>{{ $t("Name") }}</th>
                                        <td>{{ this.showed.name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t("Uploaded at") }}</th>
                                        <td>
                                            {{
                        new Date(
                            this.showed.created_at
                        ).toLocaleString()
                    }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t("Size") }}</th>
                                        <td>
                                            {{
                        (
                            this.showed.size / 1024
                        ).toFixed(2)
                    }}
                                            KB
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t("Type") }}</th>
                                        <td>
                                            <!-- <p style="overflow: hidden; text-overflow: ellipsis;"> -->
                                            {{ this.showed.mimetype }}
                                            <!-- </p> -->
                                        </td>
                                    </tr>
                                    <tr v-if="isImage">
                                        <th>{{ $t("Dimensions") }}</th>
                                        <td>
                                            {{ this.showed.height }}px x
                                            {{ this.showed.width }}px
                                        </td>
                                    </tr>
                                </table>
                                <table class="table">
                                    <tr>
                                        <td>
                                            <a :href="this.showed.url" class="link button-download-file" download>
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t("Download") }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./MediaFileComponent.ts" />
<style scoped src="./MediaFileComponent.css" />
