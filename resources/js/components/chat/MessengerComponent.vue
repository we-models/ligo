<template>
    <div>
        <language-selector-component :lngs="lngs" :lng="lng" :title="$tChoice(title.toUpperCase(), 2)" />

        <div class="messaging">
            <div class="inbox_msg">
                <conversation-component :url="url" :channels_link="channels_link"  ref="conversation"/>
                <div class="mesgs">
                    <div class="msg_history" id="chat-history">

                        <div style="padding:2em" v-if="progress">
                            <div  class="interface_loader"></div>
                        </div>


                        <div v-for="msg in messages.data" v-if="!progress">

                            <div v-if="Number(msg.transmitter) === Number(msg.channel.user1.id)"  :class="Number(this.auth_identifier) === Number(msg.channel.user1.id) && !Number(this.intermediary) ?  'outgoing_msg': 'incoming_msg'">
                                <div :class="Number(this.auth_identifier) === Number(msg.channel.user1.id) && !Number(this.intermediary) ? 'sent_msg_img': 'incoming_msg_img'">
                                    <img
                                        :src="getImageSrc(msg.channel.profile_user1)"
                                        v-if="msg.channel.profile_user1.images.length > 0"
                                        :alt="msg.channel.profile_user1?.name">
                                    <div class="icon-circle icon-chat bg-success" v-else>
                                        <h4>U1</h4>
                                    </div>
                                </div>
                                <div :class="Number(this.auth_identifier) === Number(msg.channel.user1.id) && !Number(this.intermediary) ? 'sent_msg': 'received_msg'">
                                    <span  class="intervened"  v-if="msg.is_from_intermediary && Number(this.intermediary)">{{ $t("Wrote by you as ") }} {{ msg.channel.profile_user1?.name }}</span>
                                    <p>
                                        <strong>{{ msg.channel.profile_user1?.name }} - {{ msg.channel.user1.code }}:</strong>
                                        <br>
                                        {{msg.message}}
                                    </p>
                                    <span class="time_date" v-html="Cts.reformatDateTime(msg.created_at)"> </span>
                                </div>

                            </div>

                            <div v-if="Number(msg.transmitter) === Number(msg.channel.user2.id)" :class="Number(this.auth_identifier) === Number(msg.channel.user2.id) && !Number(this.intermediary) ? 'outgoing_msg': 'incoming_msg'">
                                <div :class="Number(this.auth_identifier) === Number(msg.channel.user2.id) && !Number(this.intermediary) ? 'sent_msg_img': 'incoming_msg_img'">
                                    <img
                                        :src="getImageSrc(msg.channel.profile_user2)"
                                        v-if="msg.channel.profile_user2.images.length > 0"
                                        :alt="msg.channel.profile_user2?.name">
                                    <div class="icon-circle icon-chat bg-primary" v-else>
                                        <h4>U2</h4>
                                    </div>
                                </div>
                                <div :class="Number(this.auth_identifier) === Number(msg.channel.user2.id) && !Number(this.intermediary) ? 'sent_msg': 'received_msg'">
                                    <span class="intervened" v-if="msg.is_from_intermediary && Number(this.intermediary)">{{ $t("Wrote by you as ") }} {{ msg.channel.profile_user2?.name }}</span>
                                    <p>
                                        <strong>{{ msg.channel.profile_user2?.name }} - {{ msg.channel.user2.code }} :</strong>
                                        <br>
                                        {{msg.message}}
                                    </p>
                                    <span class="time_date" v-html="Cts.reformatDateTime(msg.created_at)"> </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="type_msg" v-if="!progress && messages.data !== undefined">

                        <ul class="nav nav-tabs" v-if="current_writer != null && Number(this.intermediary)">
                            <li class="nav-item">
                                <a
                                    :class="`nav-link ${current_writer.id === current_channel.user1.id ? 'active' : ''  }`"
                                    aria-current="page"
                                    href="#"
                                    v-on:click="changeEmitter(current_channel.user1)">
                                    {{current_channel.profile_user1.name}} - {{ current_channel.user1.code }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    :class="`nav-link ${current_writer.id === current_channel.user2.id ? 'active' : ''  }`"
                                    href="#"
                                    v-on:click="changeEmitter(current_channel.user2)">
                                    {{current_channel.profile_user2.name}} - {{ current_channel.user2.code }}
                                </a>
                            </li>
                        </ul>

                        <div class="input_msg_write">

                            <div style="display: flex">
                                <div style="flex: 10">
                                    <textarea
                                        type="text"
                                        id="chat-input"
                                        rows="2"
                                        v-model="message"

                                        @keyup.enter="sendChat()"
                                        class="form-control"
                                        style="background-color:#ffffff; resize: none"
                                        :placeholder="$t('Type a message')"></textarea>
                                </div>
                                <div style="flex: 1; text-align: center">
                                    <button type="button" class="btn btn-primary" style="margin-top:1em" v-on:click="sendChat()">
                                        {{$t('Send')}}
                                    </button>
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
import cts from '../Constants';
import {nextTick} from "vue";
    export default {
        props: ['lngs', 'lng', 'chats_link', 'server', 'port', 'title', 'url', 'channels_link', 'individual', 'csrf', 'send_chat', 'intermediary', 'auth_identifier'],
        name: "MessengerComponent",
        data() {
            return {
                Cts: cts,
                current_channel: null,
                messages : [],
                progress : false,
                current_writer : null,
                message : ""
            }
        },
        created() {
            // this.intermediary = Number(this.intermediary) ? true : false;
            // this.auth_identifier = Number(this.auth_identifier);
            this.onNewChannel();
            this.onChangeChannel();
            this.incomingMessage();
            console.log("Es intermediario", this.intermediary);
        },

        methods : {
            sendChat : function(){
                if(this.message.trim().length === 0) return;

                let date = new Date();

                let receiver = this.current_writer.id;
                receiver = (this.current_channel.user2.id === receiver) ? this.current_channel.user1.id : this.current_channel.user2.id

                let newMessage = {
                    created_at : date,
                    id : date.getTime(),
                    is_from_intermediary : true,
                    is_last : true,
                    message : this.message,
                    transmitter : this.current_writer.id,
                    channel : this.current_channel.id,
                    receiver : receiver
                }

                if (!Number(this.intermediary)) {
                    newMessage.is_from_intermediary = false;
                }

                jQuery.ajax(this.send_chat, {
                        method: 'POST',
                        data: newMessage,
                        headers: { 'X-CSRF-TOKEN': this.csrf },
                        success: (_response) => {
                            this.$refs.conversation.current_channel.messages = [_response.message]
                        },
                        error: (error)=> {
                            console.log(error);
                        }}
                );

                newMessage.channel = this.current_channel;

                this.messages.data = this.messages.data.map((msg)=>{
                    msg.is_last = false;
                    return msg;
                });

                this.messages.data.push(newMessage);
                this.message = "";
                this.scrollChat();
            },
            incomingMessage : function(){
                this.emitter.on('incoming_message', parameter => {

                    let date = new Date();
                    let message = parameter.event.message;
                    if (!Number(this.intermediary)) {
                        if(message.is_from_intermediary && Number(this.auth_identifier) == message.transmitter) return false;
                        if(!message.is_from_intermediary && Number(this.auth_identifier) != message.transmitter) return false;
                    }
                    let newMessage = {
                        created_at : date,
                        id : date.getTime(),
                        is_from_intermediary : message.is_from_intermediary,
                        is_last : true,
                        message : message.message,
                        transmitter : message.transmitter,
                        channel : this.current_channel,
                        receiver : message.receiver
                    }

                    this.messages.data = this.messages.data.map((msg)=>{
                        msg.is_last = false;
                        return msg;
                    });

                    this.messages.data.push(newMessage);
                    this.scrollChat();
                });
            },
            changeEmitter : function(profile){
                this.current_writer = profile;
                jQuery('#chat-input').focus();
            },
            scrollChat : function (){
                nextTick(()=>{
                    let chats = document.getElementById('chat-history');
                    chats.scrollTop = chats.scrollHeight;
                });
            },
            onNewChannel : function (){
                this.emitter.on('new_channel', parameter => {
                    let channel = JSON.parse(parameter.channel.channel);
                    parameter.channel.action = Number(parameter.channel.action);
                    if(parameter.channel.action === 1){
                        fetch(this.individual + '?code=' + channel.name).then(response => response.json()).then((data) => {
                            this.$refs.conversation.pagination.data.unshift(data);
                            this.$refs.conversation.listenToChannel(parameter.channel.name);
                        });
                    }else{
                        this.$refs.conversation.pagination.data = this.$refs.conversation.pagination.data.filter(ch => ch.id !== channel.id);
                        this.$refs.conversation.removeListen(parameter.channel.name);
                    }
                });
            },

            onChangeChannel : function (){
                this.emitter.on('change_channel', parameter => {
                    this.current_channel = parameter.channel;
                    this.current_writer = this.current_channel.user1;

                    if (!Number(this.intermediary)) {
                        this.current_writer = Number(this.auth_identifier) === Number(this.current_channel.user1.id) ? this.current_channel.user1 : this.current_channel.user2;
                    }
                    let receiver = this.current_writer.id;
                    receiver = (this.current_channel.user2.id === receiver) ? this.current_channel.user1.id : this.current_channel.user2.id;

                    this.progress = true;
                    fetch(`${this.chats_link}?channel=${parameter.channel.id}`)
                        .then(response => response.json()).then((data) => {
                        this.messages = data;
                        this.messages.data = this.messages.data.reverse();

                        this.messages.data.filter((datos) => !(datos.transmitter === receiver && datos.is_from_intermediary == false) && !(datos.transmitter === this.current_writer.id && datos.is_from_intermediary == true))
                    }).catch((error)=>{
                        console.log(error);
                    }).finally(()=>{
                        this.progress = false;
                        this.scrollChat();
                    });
                });
            },

            getImageSrc : function(profile){
                let image = profile.images;
                if(image.length > 0) return image[0]?.thumbnail;
                return '';
            },
        }
    }
</script>
