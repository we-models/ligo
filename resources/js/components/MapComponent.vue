<template>
    <div>
        <div class="input-group">
            <div class="form-control" style="height: auto">
                <input type="hidden" :name="'$$lat_' + name" >
                <input type="hidden" :name="'$$long_' + name">
                <label for="">{{ this.entity }}</label>
            </div>
            <span class="input-group-text" v-if="!readonly">
                <a href="javascript:void(0)" v-on:click="openModalMap" style="width: 100%">{{ $t('Select') }}</a>
            </span>
        </div>
        <div class="modal fade modal_map" tabindex="-1" role="dialog" aria-hidden="true" data-bs-theKeyboard="false">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document" v-if="show_map">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">{{$t('Select location')}}</h5>
                        <button type="button" class="close" v-on:click="closeModalMap">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="search" v-model="this.search" class="form-control pac-input">
                        </div>
                        <div class="map">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" v-on:click="closeModalMap">{{$t('Close')}}</button>
                        <button type="button" class="btn btn-primary" v-on:click="saveChanges">{{$t('Mark as selected')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import cts from './Constants';
import { nextTick } from 'vue'
export default {
    props: ['ent', 'name', 'theKey', 'lat', 'lng', 'onChange', 'readonly'],
    name: "MapComponent",
    data() {
        return {
            Cts: cts,
            entity: this.ent,
            loader : null,
            map : null,
            latitude : Number(this.lat??0),
            longitude : Number(this.lng??0),
            search : "",
            show_map : false
        }
    },
    mounted() {
        this.map = null;
        this.setValues();
    },
    methods : {
        openModalMap : function(){
            jQuery(this.$el).find('.modal_map').show();
            this.show_map = true;
            nextTick(() => {
                this.createMap();
            })

        },
        closeModalMap : function(){
            jQuery(this.$el).find('.modal_map').hide();
            this.show_map = false;
        },
        getCurrentLocation : async function(){
            let current_location = {latitude : 0, longitude : 0};
            try{
                if (navigator.geolocation) {
                    current_location = await new Promise((resolve, reject) => {
                        if (!navigator.geolocation) reject({latitude : 0, longitude : 0});
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                resolve(position.coords);
                            },
                            (error) => {
                                reject({latitude : 0, longitude : 0});
                            }
                        );
                    });
                }
            }catch (error){

            }
            this.latitude = current_location.latitude;
            this.longitude = current_location.longitude;
            const center = new google.maps.LatLng(this.latitude, this.longitude);
            this.map.panTo(center)
        },
        createMap : async function(){

            if(this.latitude === 0 && this.longitude === 0){
                this.getCurrentLocation();
            }

            let input = this.$el.querySelector(".pac-input");
            let searchBox = new google.maps.places.SearchBox(input);

            let center = { lat: this.latitude, lng: this.longitude };

            this.map = new google.maps.Map(this.$el.querySelector(".map"), {
                center: center,
                zoom: 20,
            });

            this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            this.map.addListener("bounds_changed", () => {
                searchBox.setBounds(this.map.getBounds());
            });

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();

                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        return;
                    }
                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                this.map.fitBounds(bounds);
            });

            this.setMarker()

        },
        setMarker : function(){
            let marker = new google.maps.Marker({
                map: this.map
            });

            let center = {
                lat : this.latitude,
                lng : this.longitude
            };

            marker.setPosition(center);

            this.map.addListener('click', (event) =>{
                let clickedLocation = event.latLng;
                marker.setPosition(clickedLocation);
                this.latitude = clickedLocation.lat();
                this.longitude = clickedLocation.lng();
            });
        },
        saveChanges : function(){
            this.$emit('onChange', {lat : this.latitude, lng : this.longitude});
            this.entity = `Latitude: ${ this.latitude}, Longitude:${this.longitude}`
            this.setValues();
            this.closeModalMap();
        },
        setValues(){
            let inputLat = this.$el.querySelector(`input[name='$$lat_${this.name}']`);
            inputLat.value = this.latitude;

            let inputLong = this.$el.querySelector(`input[name='$$long_${this.name}']`);
            inputLong.value = this.longitude;
        }
    }
}
</script>

<style scoped>

    .map{
        width: 100%;
        height: 700px;
    }

</style>
