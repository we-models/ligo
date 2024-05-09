<template>
    <div>

        <language-selector-component :lngs="lngs" :lng="lng" :title="$tChoice(title.toUpperCase(), 2)" />

        <div class="vue-layout" v-cloak>
            <ul class="nav nav-tabs all_tabs" role="tablist">
                <li class="nav-item" role="presentation" v-if="permissions.includes('.create')">
                    <button
                        v-on:click="setSelected('.create')"
                        class="nav-link active"
                        id="create-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#create-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="create-tab-pane"
                        aria-selected="true"
                    >
                        <i class="fa-solid fa-circle-plus"></i>
                        <span v-if="!is_mobile">{{ $t('create') }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation" v-if="permissions.includes('.all')">
                    <button
                        v-on:click="setSelected('.all')"
                        class="nav-link"
                        id="list-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#list-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="list-tab-pane"
                        aria-selected="false"
                    >
                        <i class="fa-solid fa-list"></i>
                        <span v-if="!is_mobile">{{ $t('list') }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation" v-if="permissions.includes('.logs')">
                    <button
                        v-on:click="setSelected('.logs')"
                        class="nav-link"
                        id="logs-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#logs-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="logs-tab-pane"
                        aria-selected="false"
                    >
                        <i class="fa-solid fa-list-check"></i>
                        <span v-if="!is_mobile">{{ $t('logs') }}</span>
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active form-div" id="create-tab-pane" aria-labelledby="create-tab"
                    tabindex="0" v-if="permissions.includes('.create')">

                    <div class="row">
                        <div :class="this.custom_fields.length > 0 ? 'col-12' : 'col-lg-8 col-md-10'">
                            <form-component
                                v-if="selected === '.create'"
                                http_method="POST"
                                :object="object_"
                                :fields="fields"
                                :url="create"
                                :icons="icons"
                                :csrf="csrf"
                                :custom_fields = "this.custom_fields"
                                :title="$t('Register new') + ' ' + $tChoice(title.toLowerCase(), 1)" />
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade form-div" id="list-tab-pane" role="tabpanel" aria-labelledby="list-tab"
                    tabindex="0" v-if="permissions.includes('.all')">
                    <list-component
                        :comments_url = "comments_url"
                        :object_class = "object"
                        v-if="selected === '.all'"
                        :object="object_"
                        :fields="fields"
                        :url="all"
                        :index="index"
                        :rating_types_url = "rating_types_url"
                        :csrf="csrf"
                        :permissions="permissions" />
                </div>
                <div class="tab-pane fade form-div" id="logs-tab-pane" role="tabpanel" aria-labelledby="logs-tab"
                    tabindex="0" v-if="permissions.includes('.logs')">
                    <logs-component
                        :url="this.logs"
                        v-if="selected === '.logs'" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        'object','csrf', 'title', 'fields', 'icons', 'object_', 'all', 'lngs', 'lng',
        'create', 'permissions', 'index', 'logs', 'custom_fields', 'comments_url',
        'rating_types_url'
    ],
    data() {
        return {
            selected: this.permissions.length > 0 ? [0] : "",
            is_mobile: false
        }
    },
    mounted: function () {
        this.getDimensions();
        window.addEventListener('resize', this.getDimensions);
        let tabs = this.$el.querySelector('.all_tabs li button');
        if(tabs != null) tabs.click();
    },
    unmounted: function () {
        window.removeEventListener('resize', this.getDimensions);
    },
    methods: {
        setSelected: function (permission) {
            this.selected = permission;
        },
        getDimensions: function () {
            this.is_mobile = document.documentElement.clientWidth <= 768;
        },
    }
}
</script>
<style scoped>
    [v-cloak] {
        display: none;
    }
</style>
