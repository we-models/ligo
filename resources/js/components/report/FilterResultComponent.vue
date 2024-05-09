<template>
    <div v-if="isMounted">

        <strong v-if="showName">{{item.name}}</strong>

        <div v-for="field in item.custom_fields" :style="showName?'margin-left:2em; margin-top:1em' : ''">
            <div v-if="field.layout === 'field'">
                <div class="card">
                    <label for="">{{field.name}}</label>
                    <value-result-component :field="field"/>
                </div>
            </div>
            <div v-if="field.layout === 'tab'">
                <h3 v-if="field.fields.length > 0">{{field.name}}</h3>
                <div class="row">
                    <div v-for="cf in field.fields" :class="`col-lg-${this.getDivWidth(cf, field.fields.length)}`">
                        <div class="card">
                            <label for="">{{cf.name}}</label>
                            <value-result-component :field="cf" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props : ['item', 'showName'],
    name: "FilterResultComponent",
    data(){
        return {
            isMounted : false
        }
    },
    mounted() {
        this.item.custom_fields = this.item.custom_fields.filter(f => !(f.layout === 'field' && f.value == null));

        this.item.custom_fields = this.item.custom_fields.map((field)=>{
            if(field.fields !== undefined){
                field.width =  field.fields.length > 3 ? '4' : '6';
                field.fields = field.fields.filter((f)=>{
                    if(f.status === 'relation' ){
                        return f.entity[f.slug] != null;
                    }
                    if(f.status === 'field' ){
                        return  f.value != null && f.value.value !== null;
                    }
                    return false
                });
            }
            return field;
        });

        this.isMounted = true;
    },
    methods : {
        getDivWidth : function(cf, len){
            if(cf.status === 'field') return len > 3 ? 4 : 6;

            if(cf.type === 'unique'){
                if(cf.entity[cf.slug] != null && cf.entity[cf.slug].has_custom_fields){
                    return 12;
                }else{
                    return len > 3 ? 4 : 6;
                }
            }else{
                if(cf.entity[cf.slug].length > 0 && cf.entity[cf.slug][0].has_custom_fields ){
                    return 12
                }else{
                    return len > 3 ? 4 : 6;
                }
            }
        }
    }
}
</script>

<style scoped>
    .card {
        margin-bottom: 10px;
        padding:5px;
    }
</style>
