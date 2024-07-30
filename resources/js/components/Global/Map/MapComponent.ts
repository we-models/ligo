import {nextTick, onMounted, ref} from "vue";
import cts from "@/components/Global/Constants";
import { GoogleMap } from "vue3-google-map";


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
            if(latitude.value === 0 && longitude.value === 0){

                getCurrentLocation();
            }


            let input:any = document.querySelector(".pac-input");
            let searchBox = new google.maps.places.SearchBox(input);

            let center = { lat: latitude.value, lng: longitude.value };

            map.value = new google.maps.Map(document.querySelector(".map"), {
                center: center,
                zoom: 20,
            });

            map.value.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            map.value.addListener("bounds_changed", () => {
                searchBox.setBounds(map.value.getBounds());
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
                map.value.fitBounds(bounds);
            });

            setMarker()


        };

        /**
         *
         */
        const setMarker = () => {
            let marker = new google.maps.Marker({
                map: map.value
            });

            let center = {
                lat : latitude.value,
                lng : longitude.value
            };

            marker.setPosition(center);

            map.value.addListener('click', (event) =>{
                let clickedLocation = event.latLng;
                marker.setPosition(clickedLocation);
                latitude.value = clickedLocation.lat();
                longitude.value = clickedLocation.lng();
            });
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
