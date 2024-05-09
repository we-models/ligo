<template>

    <crud-component
        v-if = "loaded"
        :comments_url    = "this.comments_url"
        :object     =   "this.object"
        :title       =   "this.title"
        :csrf        =   "this.csrf"
        :fields      =   "this.fields"
        :icons       =   "this.icons"
        :object_     =   "this.values"
        :index       =   "this.index"
        :all         =   "this.all"
        :create      =   "this.create"
        :lngs        =   "this.languages"
        :lng         =   "this.language"
        :permissions =   "this.permissions"
        :logs        =   "this.logs"
        :custom_fields = "this.custom_fields"
        :rating_types_url = "this.rating_types_url"
    />

</template>

<script>
export default {
    props : ['details'],
    name: "GlobalComponent",
    data () {
        return {
            rating_types_url : "",
            comments_url : "",
            object: "",
            title: "",
            csrf: "",
            fields: [],
            icons: [],
            values: {},
            index: "",
            all: "",
            create: "",
            languages: [],
            language: "en",
            permissions: [],
            logs: "",
            loaded : false,
            custom_fields : [],
        }
    },
    mounted() {
        console.log(this.details);
        fetch(this.details)
            .then(response => response.json())
            .then((data) => {
                this.rating_types_url = data.rating_types_url;
                this.comments_url = data.comments;
                this.object = data.object;
                this.title = data.title;
                this.csrf = data.csrf;
                this.fields = data.fields;
                this.icons = data.icons;
                this.values = data.values;
                this.index = data.index;
                this.all = data.all;
                this.create = data.create;
                this.languages = data.languages;
                this.language = data.language;
                this.permissions = data.permissions;
                this.logs = data.logs;
                this.custom_fields = data.custom_fields;
            }).finally(()=>{
                this.loaded = true;
        });
    }
}
</script>

<style scoped>

</style>
