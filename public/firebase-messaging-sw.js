importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

fetch('/get_fb_config')
    .then(res => res.json())
    .then((response)=>{
        if(!response.auth || !response.fb_enable) return;
        firebase.initializeApp({
            apiKey: response.apiKey,
            authDomain: response.authDomain,
            projectId: response.projectId,
            storageBucket: response.storageBucket,
            messagingSenderId: response.messagingSenderId,
            appId: response.appId,
            measurementId: response.measurementId
        });

        const messaging = firebase.messaging();
        messaging.onBackgroundMessage((payload) => {
            console.log('[firebase-messaging-sw.js] Received background message ', payload);
        });
        messaging.onMessage((payload) => {
            console.log('Message received. ', payload);
        });


    }).catch((error)=>{

    });
