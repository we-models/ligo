import {nextTick, onMounted, ref} from "vue";
import cts from "@/components/Global/Constants";

export default {
    emits: ["onChange"],
    props: ["ent", "name", "theKey", "lat", "lng", "onChange", "readonly"],
    name: "MapComponent",
    setup(props: any, {emit}) {
        /* Data */
        const Cts = cts;
        const entity = ref<any>();
        const loader = ref<any>(null);
        const map = ref<any>(null);
        const latitude = ref<number>(0);
        const longitude = ref<number>(0);
        const search = ref<string>("");
        const show_map = ref<boolean>(false);

        /*
         * assign prop values
         */

        onMounted(() => {
            entity.value = props.ent;
            latitude.value = Number(props.lat ?? 0);
            longitude.value = Number(props.lng ?? 0);

            map.value = null;
            setValues();
        });

        /**
         *
         */
        const openModalMap = (): void => {
            jQuery(document).find(".modal_map").show();
            show_map.value = true;
            nextTick(() => {
                createMap();
            });
        };

        /**
         *
         */
        const closeModalMap = () => {
            jQuery(document).find(".modal_map").hide();
            show_map.value = false;
        };

        /**
         *
         */
        const getCurrentLocation = async () => {
            let current_location = {latitude: 0, longitude: 0};
            try {
                if (navigator.geolocation) {
                    current_location = await new Promise((resolve, reject) => {
                        if (!navigator.geolocation)
                            reject({latitude: 0, longitude: 0});
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                resolve(position.coords);
                            },
                            (error) => {
                                reject({latitude: 0, longitude: 0});
                            }
                        );
                    });
                }
            } catch (error) {
            }
            latitude.value = current_location.latitude;
            longitude.value = current_location.longitude;
        };

        /**
         *
         */
        const createMap = async () => {
            // if(this.latitude === 0 && this.longitude === 0){
            //     this.getCurrentLocation();
            // }
            //
            // let input = this.$el.querySelector(".pac-input");
            // let searchBox = new google.maps.places.SearchBox(input);
            //
            // let center = { lat: this.latitude, lng: this.longitude };
            //
            // this.map = new google.maps.Map(this.$el.querySelector(".map"), {
            //     center: center,
            //     zoom: 20,
            // });
            //
            // this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            //
            // this.map.addListener("bounds_changed", () => {
            //     searchBox.setBounds(this.map.getBounds());
            // });
            //
            // searchBox.addListener("places_changed", () => {
            //     const places = searchBox.getPlaces();
            //
            //     if (places.length === 0) {
            //         return;
            //     }
            //
            //     // For each place, get the icon, name and location.
            //     const bounds = new google.maps.LatLngBounds();
            //
            //     places.forEach((place) => {
            //         if (!place.geometry || !place.geometry.location) {
            //             return;
            //         }
            //         if (place.geometry.viewport) {
            //             bounds.union(place.geometry.viewport);
            //         } else {
            //             bounds.extend(place.geometry.location);
            //         }
            //     });
            //     this.map.fitBounds(bounds);
            // });
            //
            // this.setMarker()
        };

        /**
         *
         */
        const setMarker = () => {
            // let marker = new google.maps.Marker({
            //     map: this.map
            // });
            //
            // let center = {
            //     lat : this.latitude,
            //     lng : this.longitude
            // };
            //
            // marker.setPosition(center);
            //
            // this.map.addListener('click', (event) =>{
            //     let clickedLocation = event.latLng;
            //     marker.setPosition(clickedLocation);
            //     this.latitude = clickedLocation.lat();
            //     this.longitude = clickedLocation.lng();
            // });
        };

        /**
         *
         */
        const saveChanges = () => {
            emit("onChange", {lat: latitude.value, lng: longitude.value});
            entity.value = `Latitude: ${latitude.value}, Longitude:${longitude.value}`;
            setValues();
            closeModalMap();
        };

        /**
         *
         */
        const setValues = () => {
            let inputLat = document.querySelector<HTMLInputElement>(
                `input[name='$$lat_${props.name}']`
            );
            inputLat.value = `${latitude.value}`;

            let inputLong = document.querySelector<HTMLInputElement>(
                `input[name='$$long_${props.name}']`
            );
            inputLong.value = `${longitude.value}`;
        };

        return {
            /* Data */
            Cts,
            entity,
            loader,
            map,
            latitude,
            longitude,
            search,
            show_map,
            /* Methods */
            openModalMap,
            closeModalMap,
            getCurrentLocation,
            createMap,
            setMarker,
            saveChanges,
            setValues,
        };
    },
};
