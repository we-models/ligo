<template>
    <nav class="navbar navbar-expand navbar-light  topbar static-top shadow" id="navbar_principal">

        <button id="sidebarToggleTop" class="btn d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
        </button>

        <ul class="navbar-nav ml-auto">

            <li class="nav-item dropdown no-arrow">
                <a type="button" class="nav-link dropdown-toggle" v-on:click="requestNotification" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="/images/bell.webp" :alt="$t('Notifications')" class="icon-img">
                    <span v-if="this.notifications !== null && this.notifications.total > 0" class="badge badge-light badge-counter" v-html="this.notifications.total" />
                </a>

                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in notify-center">
                    <h6 class="dropdown-header">
                        {{$t('Notification center')}}
                    </h6>
                    <div class="dropdown-item" v-if="notifications !== null" v-for="notification in notifications.data" >
                        <div :style="`background-color:${notification.type.background}; margin:0.3em; padding:1em`">
                            <div style="position: absolute; right: 3em">
                                <a href="#" v-on:click="markAsRead(notification)">
                                <span :style="`color:${notification.type.color}`">
                                    <i class="fa-solid fa-eye"></i>
                                </span>
                                </a>
                            </div>
                            <div class="d-flex align-items-center notify-item" >
                                <div class="mr-4">
                                    <div class="icon-circle bg-success">
                                        <img v-if="notification.images.length > 0" :src="notification.images[0].thumbnail" alt="" class="img-notify">
                                        <div  v-if="notification.images.length === 0" class="icon-circle bg-success">
                                            <h1>N</h1>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a :href="notification.link" target="_blank" class="notify-link">
                                        <div :style="`color:${notification.type.color}`">
                                            <h6> {{notification.name}} </h6>
                                            <div>
                                                {{notification.content}}
                                            </div>
                                            <div class="small">
                                                {{ Cts.reformatDateTime(notification.created_at) }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1em">
                        <nav aria-label="Page navigation" class="nav justify-content-end" v-if="notifications != null && notifications.last_page > 1">
                            <ul class="pagination">
                                <li v-for="link in notifications.links" v-bind:class="['page-item', link.active ? 'active' : '']">
                                    <button class="page-link" v-if="link.url != null" v-on:click="fillNotifications(link.url)"
                                            v-html="$t(link.label)"></button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </li>


            <div class="btn-group">
                <button type="button" class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-white-600 small" v-html="name"></span>
                    <img class="img-profile rounded-circle" width="35px" height="35px">
                </button>
                <ul class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                    <li><a class="dropdown-item" :href="change_password">
                        {{$t('Change password')}}
                    </a></li>
                </ul>
            </div>

            <div class="topbar-divider d-none d-sm-block"></div>

            <li class="nav-item">
                <a :href="logout" id="logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            </li>
        </ul>
    </nav>
</template>

<script>
import cts from './Constants';
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getMessaging, getToken, onMessage  } from "firebase/messaging";


export default {
    props : [
        'logout',
        'change_password',
        'name',
        'url_token',
        'fb_api_key',
        'fb_auth_domain',
        'fb_project_id',
        'fb_storage_butcket',
        'fb_messaging_sender',
        'fb_app_id',
        'fb_measurement_id',
        'fb_enable',
        'fb_public_key',
        'csrf',
        'fb_web_key',
        'notifications_link',
        'notifications_mark'
    ],
    name: "NavBarComponent",
    data(){
        return {
            Cts: cts,
            notifications : null
        }
    },
    created() {
        this.getNotifications();
        if(this.fb_enable)  this.mountFirebase();
    },
    methods: {
        mountFirebase : function(){
            try{
                let firebaseConfig = {
                    apiKey: this.fb_api_key,
                    authDomain: this.fb_auth_domain,
                    projectId: this.fb_project_id,
                    storageBucket: this.fb_storage_butcket,
                    messagingSenderId: this.fb_messaging_sender,
                    appId: this.fb_app_id,
                    measurementId : this.fb_measurement_id
                };

                let fb_app = initializeApp(firebaseConfig);
                getAnalytics(fb_app);
                let fcm_messaging = getMessaging(fb_app)
                //debugger
                getToken(fcm_messaging, {vapidKey : this.fb_web_key }).then((currentToken) => {
                    jQuery.ajax(this.url_token, {
                        method: 'POST',
                        data: {
                            'fcm_token' : currentToken,
                            'device' : navigator.userAgent
                        },
                        headers: { 'X-CSRF-TOKEN': this.csrf },
                        success: (_response) => {
                            console.log(_response);
                        },
                        error: (error)=> {
                            console.log("Error", error);
                        }
                    })
                }).catch((error)=>{
                    console.log(error);
                });
                onMessage(fcm_messaging, (payload) => {
                    //debugger
                    console.log(payload);
                    this.notifications.total = this.notifications.total + 1;
                    this.notifications.data.unshift(JSON.parse(payload.data.notification));
                    (new Audio(location.origin + '/sounds/notification.wav')).play();

                    if(payload.data.state === 'channel'){
                        this.emitter.emit("new_channel", {'channel' : payload.data});
                    }

                });
            }catch (error){
                console.log(error);
            }
        },
        getNotifications : function(){
            this.fillNotifications(this.notifications_link)
        },
        fillNotifications : function(url){
            fetch( url )
                .then(res => res.json())
                .then((response)=>{
                    this.notifications = response;
                }).catch((error)=>{
                    console.log(error)
            });
        },
        markAsRead : function(notification){
           jQuery.ajax(this.notifications_mark, {
              method: 'DELETE',
              data: { "_token": this.csrf, 'notification' : notification.id },
              success: (response) => {
                  this.notifications.total = this.notifications.total - 1;
                  (new Audio(location.origin + '/sounds/success.mp3')).play();
                    this.notifications.data = this.notifications.data.filter(notify => notify.id !== notification.id);
              },
               error: (error)=> {
                  console.log(error);
                   (new Audio(location.origin + '/sounds/error.ogg')).play();
               },
           });
        },
        requestNotification : function(){
            Notification.requestPermission()
        }
    }
}
</script>

<style scoped>
    .notify-center{
        left: -400px;
        top: 60px;
    }
    .img-notify{
        width: 100%;
    }
    .notify-item{
        cursor: pointer;
    }
    .notify-link{
        text-decoration: none;
        color: unset;
    }
</style>
