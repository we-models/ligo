<template>
    <div >
        <input v-if="Cts.input_types.includes(type.name)" :readonly="readonly" v-bind="getTypeAttributes()" v-model="entity" v-on:input="changeValue"
            class="form-control" :name="name">

        <div class="form-check form-switch" v-if="type.name === 'Boolean'">
            <input type="checkbox" :name="name" class="form-check-input" v-on:change="changeValue" :readonly="readonly"
                :checked="this.entity !== null && !['0', 0, false, ''].includes(this.entity.trim())">
        </div>

        <textarea class="form-control" :placeholder="this.default ?? ''" :name="name" v-model="this.entity" v-on:input="changeValue" :readonly="readonly"
            v-if="this.type.name === 'Text'">{{ this.entity }}</textarea>

        <text-area-component v-if="this.type.name === 'Html'" :ent="this.entity" :theKey="this.name" v-on:input="changeValue"
            :toolbar="Cts.quill_toolbar" :placeholder="this.default ?? ''" :fonts = "this.fonts" />

        <array-json-component v-if="this.type.name === 'Json'" :ent="this.entity.length === 0 ? '{}' : this.entity"
            :theKey="this.name" />
        <array-json-component v-if="this.type.name === 'Array'" :ent="this.entity.length === 0 ? '[]' : this.entity"
            :theKey="this.name" />

        <map-component
            v-if="this.type.name === 'Map'"
            :ent="this.renderLatLng()"
            :theKey="this.name"
            :lat = "this.field.latitude?.value"
            :lng = "this.field.longitude?.value"
            :name="name"
            :readonly = "readonly"
            @onChange="changeLatLng">

        </map-component>

    </div>
</template>

<script>
import cts from './Constants';

export default {
    props: ['ent', 'name', 'type', 'default', 'fonts', 'field', 'readonly'],
    name: "FieldComponent",
    data() {
        return {
            Cts: cts,
            entity: this.ent,
            image_attr : {
                'multiple' : false,
                'data' : {
                    'url' : `${localStorage.getItem('base')}/image/all`,
                    'store' : `${localStorage.getItem('base')}/image`,
                    "sorts" : ["created_at","name","size","extension","visibility"]
                }
            },
            lat : 0,
            lng : 0
        }
    },
    mounted(){

    },
    methods: {
        getTypeAttributes: function () {
            let vTypes = this.Cts.value_types;
            let response = {
                placeholder: this.default ?? '',
                value: this.entity,
            }
            response.type = vTypes[this.type.name].type;
            if (vTypes[this.type.name].step !== undefined) response.step = vTypes[this.type.name].step;
            return response;
        },
        changeLatLng : function(latLng){
            this.entity = `${latLng.lat}, ${latLng.lng}`
            this.lat = latLng.lat;
            this.lng = latLng.lng;
            this.$emit('onSetSelectedItems', this.name, this.entity);
        },
        renderLatLng : function(){
            if(this.field.latitude == null || this.field.longitude == null){
                return '';
            }else{
                return `Latitude: ${ this.field.latitude.value}, Longitude:${ this.field.longitude.value}`
            }
        },
        changeValue : function($event){
            if(this.type.name === 'Boolean'){
                this.entity = $event.originalTarget.checked ? '1' : '0';
            }
            this.$emit('onSetSelectedItems', this.name, this.entity);
        }
    }
}

</script>

<style scoped>

</style>
