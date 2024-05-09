<template>
    <div :style="`background-color:${this.color}`" class="padding-15">
        <div class="interface_loader" v-if="progress"></div>

        <div v-for="field in this.the_object.custom_fields" v-if="this.the_object != null">
            <field-filter-component
                :field="field"
                :readonly="this.readonly"
                :prefix="this.prefix"
                v-if="field.status === 'field' && field.layout === 'field'"
            />
            <object-filter-component
                :field="field"
                :readonly="this.readonly"
                :filter_link ="this.filter_link"
                v-if="field.status === 'relation'"
                :prefix="this.prefix"
            />
            <div v-if="field.status === 'field' && field.layout === 'tab' && field.fields.length > 0"  >
                <div class="form-group">
                    <div class="card">
                        <div class="card-header" :id="'cf_heading_' + field.id">
                            <button v-on:click="toggleCF('#cf_collapse_' + field.id)" type="button" class="btn btn-link">
                                {{field.name}}
                            </button>
                        </div>
                        <div :id="'cf_collapse_' + field.id" class="collapse show" >
                            <div class="card-body">
                                <div class="row">
                                    <div :class="'col-lg-' + tab_field.row" v-for="tab_field in field.fields">
                                        <field-filter-component
                                            :field="tab_field"
                                            :readonly="this.readonly"
                                            :prefix="this.prefix"
                                            v-if="tab_field.layout === 'field'"
                                        />
                                        <object-filter-component
                                            :field="tab_field"
                                            :readonly="this.readonly"
                                            v-if="tab_field.status === 'relation'"
                                            :csrf = "this.csrf"
                                            :filter_link ="this.filter_link"
                                            :progress = "this.progress"
                                            :prefix="this.prefix"
                                        />
                                    </div>
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

import ObjectFilterComponent from "./ObjectFilterComponent";
export default {
    components: {ObjectFilterComponent},
    props : ['object_type', 'filter_link', 'readonly', 'csrf', 'onFiltersApplied', 'prefix'],
    name: "ObjectTypeFieldsFilterComponent",
    data(){
        return {
            the_object : null,
            progress : false,
            color : '#fffffff',
            filters : []
        }
    },
    mounted() {
        this.getColor();
        this.init();
    },
    methods : {
        init : function (){
            if(this.object_type == null){
                this.custom_fields = null;
            }else{
                this.progress = true;
                fetch(`${this.filter_link}?object_type=${this.object_type.id}`)
                    .then(response => response.json())
                    .then((data) => {
                        this.the_object = data;
                        this.the_object.custom_fields.forEach((cf)=>{
                            cf['row'] = 6;
                            if(!['field', 'relation'].includes(cf.status) || cf.layout === "tab"){
                                cf.fields = cf.fields.map((f)=>{
                                    f.row = 6;
                                    return f;
                                });
                            }
                        });
                    }).finally(()=>{
                        this.progress = false;
                    });
            }
        },
        toggleCF : function(id){
            $(id).collapse('toggle')
        },
        getColor : function(){
            const r = Math.floor(Math.random() * 128) + 128;
            const g = Math.floor(Math.random() * 128) + 128;
            const b = Math.floor(Math.random() * 128) + 128;
            this.color = `rgba(${r},${g},${b}, 0.5)`;
        },
    }
}
</script>

<style scoped>

</style>
