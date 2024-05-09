<template>
    <div>
        <div class="modal fade" id="commentModal" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="commentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="rating-header">
                            <div>
                                <h3>{{ $t('Comments for')  }} {{object.name}}</h3>
                            </div>
                            <div>
                                <table>
                                    <tr v-for="ratingType in this.rating_types">
                                        <td>
                                            <strong>
                                                <small>{{ratingType.name}}</small>
                                            </strong>
                                        </td>
                                        <td>
                                            <StarRating :star-size="10" :read-only="true" v-model:rating="ratingType.average"></StarRating>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" v-on:click="closeModal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <progress-component :progress="this.progress_value" />

                            <div class="comment-list">
                                <div class="interface_loader" v-if="loading"></div>

                                <div v-for="page in pagination.data" v-if="pagination != null">

                                    <div class="card" style="margin: 20px 0">
                                        <div style="display: flex;  justify-content:space-between" class="card-header">
                                            <span >{{ page.user.name }}</span>
                                            <span> {{ Cts.reformatDateTime(page.created_at)}}</span>
                                        </div>
                                        <div class="card-body">
                                            <div style="display: flex">
                                                <div style="flex: 3">
                                                    <p class="card-text">
                                                        {{page.comment}}
                                                    </p>
                                                </div>
                                                <div style="flex: 1">
                                                    <table>
                                                        <tr v-for="rating in page.ratings">
                                                            <td style="padding:10px">{{rating.rating_type.name}}</td>
                                                            <td style="padding:10px">
                                                                <StarRating :star-size="15" v-model:rating="rating.rating" :read-only="true"></StarRating>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>

                            <div v-if="pagination != null">
                                <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1">
                                    <ul class="pagination">
                                        <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                                            <button class="page-link" v-if="link.url != null" v-on:click="fillList(link.url)"
                                                    v-html="$t(link.label)"></button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>


                            <div class="rating-edit">
                                <div style="flex: 5">
                                   <div style="padding-bottom: 2em">
                                       <table class="table">
                                           <tr v-for="ratingType in this.rating_types">
                                               <td>
                                                   <strong>
                                                       {{ratingType.name}}
                                                   </strong>
                                               </td>
                                               <td>
                                                   <StarRating :star-size="15" v-model:rating="ratingType.value"></StarRating>
                                               </td>
                                           </tr>
                                       </table>
                                   </div>
                                    <textarea class="form-control" v-model="to_comment" name="" id="" cols="30" rows="5"></textarea>
                                </div>
                                <div style="flex: 2; text-align: center; vertical-align: middle">
                                    <button type="button" v-on:click="createNew()" class="btn btn-primary"> {{ $t('Comment') }} </button>
                                </div>
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
    import cts from './Constants'
    import StarRating from 'vue-star-rating'
    export default {
        props: ['object', 'comments_url', 'csrf', 'rating_types_url'],
        name: "ModalCommentComponent",
        components :{
            StarRating
        },
        data(){
            return {
                Cts : cts,
                loading: false,
                search: "",
                pagination: [],
                paginate: 5,
                current_link: this.comments_url,
                url_parameter : "",
                to_comment : "",
                progress_value : 0,
                rating_types : []
            }
        },
        mounted() {

            this.getParams(this.current_link);

            this.emitter.on("fillComments", params => {
                this.getRatingTypes();
                jQuery('#commentModal').show();
                this.fillList(this.current_link);
            });
        },
        methods: {
            getRatingTypes : function(){
                let uri = `${this.rating_types_url}?object_type=${this.object.object_type.id}&object=${this.object.id}`;
                console.log(uri);
                fetch(uri)
                    .then(response => response.json())
                    .then((data) => {
                        this.rating_types = data.map((ratType)=>{
                            ratType.value = 0;
                            ratType.average = Number(ratType.average);
                            return ratType;
                        });
                    });
            },
            getParams : function (url) {
                url = new URL(url);
                const urlParams = new URLSearchParams(url.search);
                for (let paramName of urlParams.keys()) {
                    let prefix = this.url_parameter.includes('?') ? "&" : "";
                    this.url_parameter += `${prefix}${paramName}=${urlParams.get(paramName)}`;
                }
            },
            encodeURL: function (uri) {
                let isNotFirst = false;
                if (uri === "") {
                    uri = this.comments_url;
                    isNotFirst = true;
                }
                if(!isNotFirst && this.url_parameter.length > 0){
                    let prefix = this.url.includes('?') ? "&" : "?";
                    uri +=  `${prefix}${this.url_parameter}`;
                }

                this.current_link = uri;
                uri = this.Cts.fillUrlParameters(uri, 'object', this.object.id);
                uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
                if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
                if (this.paginate !== 10) uri = this.Cts.fillUrlParameters(uri, 'paginate', this.paginate);
                return uri;
            },

            closeModal: function () {
                jQuery('#commentModal').hide();
                this.object_ = null;
                this.rating_types = [];
            },
            fillList : function(uri){
                this.pagination = [];
                let the_uri = this.encodeURL(uri);
                this.loading = true
                console.log(the_uri);
                fetch(the_uri).then(response => response.json())
                    .then((data) => {
                        this.pagination = data;
                    }).finally(()=>{
                    this.loading = false;
                });
            },
            createNew : function(){
                let replaced = this.comments_url.replace('all', 'save');
                jQuery.ajax(replaced, {
                    method: 'POST',
                    data: {
                        comment : this.to_comment,
                        object : this.object.id,
                        ratings : this.rating_types
                    },
                    headers: { 'X-CSRF-TOKEN': this.csrf },
                    xhr: () => {
                        let xhr = new XMLHttpRequest();
                        xhr.upload.onprogress = (e) => {
                            this.progress_value = Math.round((e.loaded / e.total) * 98);
                        };
                        return xhr;
                    },
                    success: (_response) => {
                        (new Audio(location.origin + '/sounds/success.mp3')).play();
                        this.pagination.data.unshift(_response);
                        this.to_comment = "";
                    },
                    error: (_error) => {
                        console.log(_error);
                    },
                    complete: () => {
                        this.progress_value = 0;
                        this.to_comment = "";
                        this.getRatingTypes();
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .comment-list{
        min-height: 400px;
        max-height: 400px;
        overflow: scroll;
    }
</style>
