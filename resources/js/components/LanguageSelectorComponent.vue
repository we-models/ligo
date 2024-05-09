<template>
    <nav class="navbar navbar-dark bg-dark vue-nav" >
        <div class="container-fluid">
            <h3 class="vue-header" v-html="title"></h3>
            <div class="d-flex">
                <a href="javascript:void(0)" v-for="lang_ in this.langs"
                    :class="'nav-lng ' + (lang_ === lang ? 'active' : '')" v-on:click="changeLang(lang_)">
                    {{ lang_.toUpperCase() }}
                </a>
            </div>
        </div>
    </nav>
</template>

<script>
import { getActiveLanguage, loadLanguageAsync } from "laravel-vue-i18n";

export default {
    props: ['title', 'lngs', 'lng'],
    name: "LanguageSelectorComponent",
    data() {
        return {
            lang: this.lng,
            langs: (typeof this.lngs) == 'string' ? JSON.parse(this.lngs) : this.lngs
        }
    },
    created() {
        this.changeLang(this.lng);
    },
    methods: {
        changeLang: function (lng) {
            loadLanguageAsync(lng).then(() => {
                this.lang = getActiveLanguage();
            }).finally(() => {
                let path = location.pathname;
                path = path.split('/');
                if (path[1] !== lng) {
                    path[1] = lng;
                    location.pathname = path.join('/')
                }
            });
        }
    }
}
</script>
