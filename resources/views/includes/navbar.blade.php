<?php
?>
<div>
   <nav-bar-component
       logout = "{{route('logout', app()->getLocale() )}}"
       name = "{{Auth::user()->name}}"
       csrf        =   "{{csrf_token()}}"
       url_token = "{{ route('user.fcm_Save') }}"
       change_password = "{{route('user.change_password', app()->getLocale())}}"
       fb_api_key = "{{ getConfigValue('GOOGLE_FIREBASE_API_KEY') }}"
       fb_auth_domain = "{{getConfigValue('GOOGLE_FIREBASE_AUTH_DOMAIN')}}"
       fb_project_id = "{{getConfigValue('GOOGLE_FIREBASE_PROJECT_ID')}}"
       fb_storage_butcket = "{{getConfigValue('GOOGLE_FIREBASE_STORAGE_BUCKET')}}"
       fb_messaging_sender = "{{getConfigValue('GOOGLE_FIREBASE_MESSAGING_SENDER_ID')}}"
       fb_app_id = "{{getConfigValue('GOOGLE_FIREBASE_APP_ID')}}"
       fb_measurement_id = "{{getConfigValue('GOOGLE_FIREBASE_MEASUREMENT_ID')}}"
       fb_enable ="{{getConfigValue('GOOGLE_FIREBASE_ENABLE')}}"
       fb_public_key = "{{getConfigValue('GOOGLE_FIREBASE_PUBLIC')}}"
       fb_web_key = "{{ getConfigValue('GOOGLE_FIREBASE_WEB')  }}"
       notifications_link = "{{ route('notification.by_user', app()->getLocale()) }}"
       notifications_mark = "{{ route('notification.mark_as_read', app()->getLocale()) }}"
   ></nav-bar-component>
</div>
