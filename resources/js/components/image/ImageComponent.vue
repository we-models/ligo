<template>
    <div style="padding: 1em">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upload-image" data-bs-toggle="tab"
                    data-bs-target="#upload-image-pane" type="button" role="tab" aria-controls="upload-image-pane"
                    aria-selected="true" v-on:click="toggleImageList()">
                    <span v-if="!is_mobile">{{ $t('Upload images') }}</span>
                    <i v-if="is_mobile" class="fa-solid fa-square-plus"></i>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="image-list" data-bs-toggle="tab" data-bs-target="#image-list-pane"
                    type="button" role="tab" aria-controls="image-list-pane" aria-selected="false"
                    v-on:click="toggleImageList()">
                    <span v-if="!is_mobile">{{ $t('List of images') }}</span>
                    <i v-if="is_mobile" class="fa-sharp fa-solid fa-border-all"></i>
                </button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="upload-image-pane" role="tabpanel" aria-labelledby="upload-image"
                tabindex="0">
                <image-upload-component
                    v-if="!this.showList"
                    :post="this.post"
                    :csrf="this.csrf"
                    :multiple="this.multiple"
                    :is_mobile ="this.is_mobile"
                />
            </div>
            <div class="tab-pane fade" id="image-list-pane" role="tabpanel" aria-labelledby="image-list" tabindex="0" >
                <div style="padding: 1em">
                    <image-list-component
                        v-if="this.showList"
                        :url="this.url"
                        :multiple="this.multiple"
                        :sorts="this.sorts"
                        :is_mobile ="this.is_mobile"
                        :quantity="(!this.is_mobile) ? this.quantity : 12"
                        :itemsSelected ="this.itemsSelected"
                        :selectable="this.selectable"
                        @onSelected="this.setChosen"
                        @onShowDetails="this.showImageModal"
                    />
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="imageModal" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="formEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" v-if="this.showed !== null">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">{{ this.showed.name }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ProgressComponent :progress="this.progress"/>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card img-card-container" style="height: 100%">
                                    <img class="img_modal" v-if="!is_mobile" :src="this.showed.large" :alt="this.showed.name">
                                    <img class="img_modal" v-if="is_mobile" :src="this.showed.medium" :alt="this.showed.name">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <table class="table">
                                    <tr>
                                        <th>{{ $t('Name') }}</th>
                                        <td>{{ this.showed.name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('Uploaded at') }}</th>
                                        <td>
                                            {{(new Date(this.showed.created_at)).toLocaleString()}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('Size') }}</th>
                                        <td>{{ (this.showed.size / 1024).toFixed(2) }} KB</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('Type') }}</th>
                                        <td>{{ this.showed.mimetype }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('Dimensions') }}</th>
                                        <td>{{ this.showed.height }}px x {{ this.showed.width }}px </td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('Visibility') }}</th>

                                        <div class="form-group">
                                            <div class="input-group" >
                                                <div class="form-control" style="height: auto">
                                                    <select name="" v-model="this.showed.visibility" id=""
                                                            class="form-control">
                                                        <option :selected="this.showed.visibility === 'public'"
                                                                value="public">{{ $t('Public') }}</option>
                                                        <option :selected="this.showed.visibility === 'business'"
                                                                value="business">{{ $t('For Business') }}</option>
                                                        <option :selected="this.showed.visibility === 'private'"
                                                                value="private">{{ $t('Private') }}</option>
                                                    </select>
                                                </div>
                                                <span class="input-group-text" >
                                                <a href="javascript:void(0)" style="width: 100%">
                                                    <i class="fa-solid fa-floppy-disk"></i>
                                                </a>
                                            </span>
                                            </div>
                                        </div>
                                    </tr>
                                </table>
                                <table class="table">
                                    <tr>
                                        <th colspan="2" style="text-align:center">
                                            {{ $t('Download') }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a class="link" download :href="this.showed.thumbnail">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Thumbnail') }}
                                            </a>
                                        </td>
                                        <td>
                                            <a class="link" download :href="this.showed.small">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Small') }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a class="link" download :href="this.showed.medium">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Medium') }}
                                            </a>
                                        </td>
                                        <td>
                                            <a class="link" download :href="this.showed.url">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Normal') }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a class="link" download :href="this.showed.large">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Large') }}
                                            </a>
                                        </td>
                                        <td>
                                            <a class="link" download :href="this.showed.xlarge">
                                                <i class="fa-sharp fa-solid fa-cloud-arrow-down"></i>
                                                {{ $t('Extra Large') }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
export default {
    props: ['post', 'csrf', 'multiple', 'url', 'multiple', 'sorts', 'quantity', 'selectable', 'setChosen', 'itemsSelected'],
    name: "ImageComponent",
    data() {
        return {
            is_mobile: false,
            paginate: this.quantity,
            search: "",
            pagination: [],
            progress_list: false,
            sort: 'created_at',
            sort_direction: 'desc',
            showed: null,
            showList : false,
            progress : 20
        }
    },
    mounted: function () {
        this.getDimensions();
        window.addEventListener('resize', this.getDimensions);
    },
    unmounted: function () {
        window.removeEventListener('resize', this.getDimensions);
    },
    methods: {
        toggleImageList : function (){
            this.showList = !this.showList;
        },
        showImageModal: function (item) {
            this.showed = item;
            jQuery('#imageModal').show();
        },
        closeModal: function () {
            this.showed = null;
            jQuery('#imageModal').hide();
        },
        getDimensions: function () {
            this.is_mobile = document.documentElement.clientWidth <= 768;
        },
    }
}
</script>

<style scoped>
    .modal-lg {
        max-width: 1200px;
    }

    .img_modal {
        width: 100%;
        margin: auto;
    }
    .form-group{
        margin-bottom: 0px;
    }

    select.form-control{
        border:none;
    }

</style>
