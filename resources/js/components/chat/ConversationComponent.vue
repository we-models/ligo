<template>
    <div class="inbox_people">
        <div class="headind_srch">
            <div class="input-group mb-3">
                    <span class="input-group-text btn_link_span">
                        <i v-on:click="fillList('')" class="fa-solid fa-rotate"></i>
                    </span>
                <input type="search" class="form-control search_form" :placeholder="$t('Search')" v-model="search"
                       v-on:keyup="fillList('')">
            </div>
        </div>
        <div class="inbox_chat">
            <div style="padding:2em" v-if="progress">
                <div  class="interface_loader"></div>
            </div>
            <div :class="getChannelClass(channel)" v-on:click="changeCurrentChannel(channel)" v-for="channel in pagination.data"> <!--active_chat-->
                <div class="chat_people">
                    <div class="row">
                        <div class="col-lg-2 col-md-4">
                            <div class="chat_img">
                                <img v-if="channel.profile_user1.images.length > 0" :src="getImage(channel.profile_user1)" :alt="channel.profile_user1?.name">
                                <div v-if="channel.profile_user1.images.length === 0" class="icon-circle icon-chat bg-success">
                                    <h4>U1</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="chat_img">
                                <img v-if="channel.profile_user2.images.length > 0" :src="getImage(channel.profile_user2)" :alt="channel.profile_user2?.name">
                                <div v-if="channel.profile_user2.images.length === 0" class="icon-circle icon-chat bg-primary">
                                    <h4>U2</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-12">
                            <div class="chat_ib">
                                <h5>{{channel.profile_user1?.name}} - {{channel.profile_user2?.name}}</h5>
                                <p v-if="channel.messages.length > 0">{{channel.messages[0].message}}</p>
                                <p v-if="channel.messages.length === 0">{{$t('Init new message')}}</p>
                                <div v-if="channel.messages.length > 0 && !channel.messages[0].is_from_intermediary" >
                                    <span class="badge bg-primary">{{$t('New')}}</span>
                                </div>
                                <div class="chat_date">
                                    <span v-if="channel.messages.length > 0" v-html="Cts.reformatDateTime(channel.messages[0].created_at)"> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="pagination.last_page > 1" >
            <ul class="pagination">
                <li v-for="link in pagination.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                    <button class="page-link" v-if="link.url != null" v-on:click="fillList(link.url)"
                            v-html="$t(link.label)"></button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
import cts from '../Constants';

export default {
    props: ['url', 'channels_link'],
    name: "ConversationComponent",
    data() {
        return {
            Cts: cts,
            url_parameter : "",
            pagination : [],
            progress : false,
            search : "",
            channels : [],
            current_channel: null,
            current_link : this.url
        }
    },
    created() {
        this.getParams(this.current_link);
        this.fillList("");
        this.getChannels();
    },
    methods : {
        fillList: function (uri) {
            this.pagination = [];
            this.progress = true;
            let the_uri = this.encodeURL(uri);
            fetch(the_uri).then(response => response.json()).then((data) => {
                this.pagination = data;
            }).catch((error)=>{

            }).finally(() => {
                this.progress = false;
            });
        },
        encodeURL: function (uri) {
            let isNotFirst = false;
            if (uri === "") {
                uri = this.url;
                isNotFirst = true;
            }
            if(!isNotFirst && this.url_parameter.length > 0){
                let prefix = this.url.includes('?') ? "&" : "?";
                uri +=  `${prefix}${this.url_parameter}`;
            }

            this.current_link = uri;
            if (this.search !== '') uri = this.Cts.fillUrlParameters(uri, 'search', this.search);
            return uri;
        },
        getChannelClass : function(channel){
            let response = 'chat_list';
            if(this.current_channel == null) return 'chat_list';
            if(this.current_channel.id === channel.id)  response += " active_chat";
            return response;
        },
        changeCurrentChannel : function(channel){
            this.current_channel = channel;
            this.emitter.emit('change_channel', {channel : channel});
        },
        getImage : function(profile){
            let image = profile.images;
            if(image.length > 0) return image[0]?.thumbnail;
            return '';
        },
        getParams : function (url) {
            url = new URL(url);
            const urlParams = new URLSearchParams(url.search);
            for (let paramName of urlParams.keys()) {
                let prefix = this.url_parameter.includes('?') ? "&" : "";
                this.url_parameter += `${prefix}${paramName}=${urlParams.get(paramName)}`;
            }
        },
        getChannels : function(){
            fetch(this.channels_link).then(response => response.json())
                .then((data) => {
                    this.channels = data;
                    this.channels.forEach((channel) => {
                        this.listenToChannel(channel)
                    });
                })
        },
        listenToChannel : function (channel){
            window.Echo.private('private-chat.' + channel)
                .listen('NewMessageEvent', this.whenChannelListen);
        },
        removeListen : function(channel){
            window.Echo.leaveChannel('private-chat.' + channel)
        },
        whenChannelListen : function (event){
            this.emitter.emit('incoming_message', {event : event});
            console.log('TestEvent received:', event);
        },
    }
}
</script>

<style scoped>

</style>
