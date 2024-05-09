<template>
    <div>
        <div v-if="field.status === 'field'">
            <strong v-if="field.value !== null">
                {{ getBooleans }}
            </strong>
            <div v-if="field.type.id === 15">
                <a target="_blank" :href="field.entity[field.slug].url">{{field.entity[field.slug].name}}</a>
            </div>
            <div v-if="field.type.id === 14">
                <a target="_blank" :href="field.entity[field.slug].url">
                    <img class="img-field" :src="field.entity[field.slug].name" :alt="field.entity[field.slug].name">
                </a>
            </div>
        </div>

        <div v-if="field.status === 'relation' && !ifHasCustomFields">
            <strong v-if="field.type === 'unique' ">
                {{field.entity[field.slug].id }} : {{field.entity[field.slug].name }}
            </strong>
            <div v-if="field.type === 'multiple'">
                <ul>
                    <li v-for="f in field.entity[field.slug]">
                        {{ f.id }} : {{ f.name }}
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="field.status === 'relation' && ifHasCustomFields">
            <filter-result-component :show-name="true" v-if="field.type === 'unique'" :item="field.entity[field.slug]" />
            <ul v-if="field.type === 'multiple'">
                <li v-for="i in field.entity[field.slug]">
                    <filter-result-component :show-name="true" :item="i" />
                </li>
            </ul>
        </div>
    </div>


</template>

<script>
import FilterResultComponent from "./FilterResultComponent";
import {trans} from "laravel-vue-i18n";
export default {
    components: {FilterResultComponent},
    props : ['field'],
    name: "ValueResultComponent",
    mounted() {

    },
    computed : {
        getBooleans : function(){
            if(this.field.value.value === 'on'){
                return trans('Yes');
            }
            return this.field.value.value;
        },
        ifHasCustomFields : function (){
            if(this.field.status === 'field') return false;
            if(this.field.type === 'unique'){
                return this.field.entity[this.field.slug].has_custom_fields;
            }else{
                let hasCf = this.field.entity[this.field.slug].filter((cf)=> cf.has_custom_fields);
                return hasCf.length > 0;
            }
        }
    }
}
</script>

<style scoped>

</style>
