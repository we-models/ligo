import {onMounted, ref} from "vue";
import {v4 as uuidv4} from "uuid";

/* COMPONENTS */
import ListComponent from "@/components/Global/List/ListComponent.vue";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";

export default {
    name: "ObjectFieldComponent",
    components: {InterfaceLoaderComponent, ListComponent},
    emits: ["onSetSelectedItems"],
    props: [
        "ent",
        "theKey",
        "value",
        "csrf",
        "required",
        "readonly",
        "multiple",
        "paginate",
    ],

    setup(props: any, {emit}) {
        /* Data */
        const modalOpened = ref<boolean>(false);
        const modal_object = ref<any>(null);
        const modal_fields = ref<any>(null);
        const modal_url = ref<any>(null);
        const modal_current = ref<any>(null);
        const modal_id = uuidv4().toString();
        const entity = ref<any>(null);
        const modal_choose_name = ref<any>("");

        /*
         * assign prop values
         */
        onMounted(() => {
            entity.value = props.ent;
        });

        /**
         *
         */
        const closeModal = () => {
            jQuery("#" + modal_id).hide();
            modalOpened.value = false;
            modal_object.value = null;
            modal_fields.value = null;
            modal_url.value = null;
            modal_current.value = null;
        };

        /**
         * @param value
         * @param theKey
         *
         */
        const openModalGeneral = (value: any, theKey: any) => {
            if (!props.multiple) openModal(value, theKey);
        };

        /**
         * @param value
         * @param theKey
         *
         */
        const openModal = (value: any, theKey: any) => {
            jQuery("#" + modal_id).show();
            modal_object.value = value.data.values;
            modal_fields.value = value.data.fields;
            modal_choose_name.value = value.name;
            modal_url.value = value.data.url;
            modalOpened.value = true;
            modal_current.value = theKey;
        };

        /**
         * @param selected
         *
         */
        const markSelected = (selected: any) => {
            closeModal();
            selected = !props.multiple ? selected[0] : selected;
            entity.value[props.theKey] = selected;
            emit("onSetSelectedItems", props.theKey, selected);
        };

        /**
         * @param op
         *
         */
        const removeItem = (op: any) => {
            entity.value[props.theKey] = entity.value[props.theKey].filter(
                (item) => item.id !== op.id
            );
            emit(
                "onSetSelectedItems",
                props.theKey,
                entity.value[props.theKey]
            );
        };


        return {
            /* Data */
            modalOpened,
            modal_object,
            modal_fields,
            modal_url,
            modal_current,
            modal_id,
            entity,
            modal_choose_name,
            /* Methods */
            closeModal,
            openModalGeneral,
            openModal,
            markSelected,
            removeItem
        }
    },
};
