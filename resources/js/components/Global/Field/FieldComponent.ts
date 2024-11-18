import {onMounted, ref} from "vue";
import cts from "@/components/Global/Constants";

/* COMPONENTS */
import MapComponent from "@/components/Global/Map/MapComponent.vue";

/* TYPES */
import type {FieldLatLngType, FieldResponseType,} from "@/types/global/internal/FieldType";

export default {
    components: {MapComponent},
    props: ["ent", "name", "type", "default_", "fonts", "field", "readonly"],
    emits: ["onSetSelectedItems"],
    name: "FieldComponent",

    setup(props: any, {emit}) {

        /* Data  */
        const Cts: any = cts;
        const entity = ref<string>("");
        const lat = ref<number>(0);
        const lng = ref<number>(0);
        const image_attr = {
            multiple: false,
            data: {
                url: `${localStorage.getItem("base")}/image/all`,
                store: `${localStorage.getItem("base")}/image`,
                sorts: ["created_at", "name", "size", "extension"],
            },
        };

        onMounted(() => {
            entity.value = props.ent;
        });

        /** Methods */

        /**
         * @return FieldResponseType
         **/
        const getTypeAttributes = (): FieldResponseType => {
            let vTypes = Cts.value_types;
            let response: FieldResponseType = {
                placeholder: props.default_ ?? "",
                value: entity.value,
                type: vTypes[props.type.name].type,
                step: null,
            };
            // response.type = vTypes[this.type.name].type;
            if (vTypes[props.type.name].step !== undefined)
                response.step = vTypes[props.type.name].step;
            return response;
        };

        /**
         * @param latLng
         */
        const changeLatLng = (latLng: FieldLatLngType): void => {
            entity.value = `${latLng.lat}, ${latLng.lng}`;
            lat.value = latLng.lat;
            lng.value = latLng.lng;
            emit("onSetSelectedItems", props.name, entity.value);
        };

        /**
         * @return string
         */
        const renderLatLng = (): string => {
            if (props.field === undefined) return '';
            if (props.field.latitude == null || props.field.longitude == null) {
                return "";
            } else {
                return `Latitude: ${props.field.latitude.value}, Longitude:${props.field.longitude.value}`;
            }
        };

        /**
         * @param event
         */
        const changeValue = ($event: any): void => {
            if (props.type.name === "Boolean") {
                entity.value = $event.originalTarget.checked ? "1" : "0";
            }
            emit("onSetSelectedItems", props.name, entity.value);
        };

        return {
            /* Data */
            Cts,
            entity,
            lat,
            lng,
            image_attr,
            /* Methods */
            getTypeAttributes,
            changeLatLng,
            renderLatLng,
            changeValue,
        };
    },
};
