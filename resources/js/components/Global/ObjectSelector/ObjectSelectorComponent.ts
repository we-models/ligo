import {computed, onMounted, ref} from "vue";
import {v4 as uuidv4} from "uuid";
import {useI18n} from "vue-i18n";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";

export default {
    components: {InterfaceLoaderComponent},
    emits: ["onSetSelectedItems"],
    props: [
        "ent",
        "llave",
        "readonly",
        "required",
        "value",
        "csrf",
        "multiple",
        "paginate",
        "type",
        "depends",
        "accept",
        "field_id",
        "field_layout",
        "filling_method",
    ],
    name: "ObjectSelectorComponent",

    setup(props: any, {emit}) {
        /* Data */
        const {t} = useI18n();
        const entity = ref<any>([]);
        const modal_id = uuidv4().toString();
        const modal_form_id = uuidv4().toString();
        const current = ref<any>(null);
        const modal_open = ref<boolean>(false);
        const reloaded = ref<boolean>(false);
        const theKey = ref<any>();
        const error = ref<any>(null);
        const dataModal = ref<any>({
            title: "",
            csrf: "",
            fields: [],
            icons: [],
            values: {},
            index: "",
            all: "",
            create: "",
            languages: [],
            language: "en",
            permissions: [],
            logs: "",
            loaded: false,
            custom_fields: [],
            loading: false,
        });

        /*
         * Assign prop values
         */
        onMounted(() => {
            entity.value = props.ent;
            theKey.value = props.llave;

            if (!props.multiple && Array.isArray(entity.value[theKey.value])) {
                entity.value[theKey.value] = entity.value[theKey.value][0];
            }
        });

        /**
         *
         */
        const closeModal = () => {
            modal_open.value = false;
            jQuery("#" + modal_id).hide();
            current.value = null;
        };

        /**
         * @param value
         * @param theKey
         */
        const openModalGeneral = (value: any, theKey: any) => {
            if (props.readonly || props.isForCreation) return;
            current.value = value;
            modal_open.value = true;
            jQuery("#" + modal_id).show();
        };

        /**
         * @param selected
         */
        const markSelected = (selected: any) => {
            closeModal();
            selected = !props.multiple ? selected[0] : selected;
            entity.value[theKey.value] = selected;
            emit("onSetSelectedItems", theKey.value, selected);
        };


        /**
         * @param file
         */
        const getImageFromFile = (file: any) => {
            let fmt = file.mimetype;
            let image = "/image_system/mimetypes/";
            if (fmt.includes("pdf")) return `${image}pdf.png`;
            if (fmt.includes("video")) return `${image}video.png`;
            if (fmt.includes("audio")) return `${image}audio.png`;
            if (
                fmt.includes("rar") ||
                fmt.includes("zip") ||
                fmt.includes("gzip")
            )
                return `${image}compressed.png`;
            if (fmt.includes("word")) return `${image}word.png`;
            if (fmt.includes("powerpoint") || fmt.includes("presentation"))
                return `${image}powerpoint.png`;
            if (fmt.includes("excel")) return `${image}excel.png`;
            return `${image}text.png`;
        };

        const showImageFileOrIcon = (data:any)=> {
            if (data.images.length > 0) {
                return data.images[0].url;
            }
            else {
                return getImageFromFile(data);
            }
        }

        const isEmpty = ()=>{
            if(entity.value[theKey.value] == null) return true;
            if(props.multiple){
                return entity.value[theKey.value].length == 0;
            }else{
                return false;
            }
        }

        const isForCreation = computed(()=>{
            return props.filling_method == 'creation' || props.filling_method == 'all'
        });


        /**
         * @param op
         */
        const removeItem = (op: any) => {
            entity.value[theKey.value] = entity.value[theKey.value].filter(
                (item) => item.id !== op.id
            );
            emit(
                "onSetSelectedItems",
                theKey.value,
                entity.value[theKey.value]
            );
        };

        /**
         * @param uri
         */
        const getDependences = (uri: any) => {
            for (let i = 0; i < props.depends.length; i++) {
                uri += (uri.includes("?") ? "&" : "?") + props.depends[i];
            }
            return uri;
        };

        /**
         * @param item
         */
        const removeFromSelector = (item) => {
            entity.value[item] = null;
            emit("onSetSelectedItems", theKey.value, null);
        };

        /**
         * @param link
         */
        const openModalForm = (link: any) => {
            var uri_separator = link.split("?");
            var index = uri_separator[0];
            var uri = index + "/details";
            if (uri_separator.length > 1) {
                uri += "?".concat(uri_separator[1]);
            }
            dataModal.value.loading = true;
            fetch(uri)
                .then((response) => response.json())
                .then((data) => {
                    dataModal.value.title = data.title;
                    dataModal.value.csrf = data.csrf;
                    dataModal.value.fields = data.fields;
                    dataModal.value.icons = data.icons;
                    dataModal.value.values = data.values;
                    dataModal.value.index = data.index;
                    dataModal.value.all = data.all;
                    dataModal.value.create = data.create;
                    dataModal.value.languages = data.languages;
                    dataModal.value.language = data.language;
                    dataModal.value.permissions = data.permissions;
                    dataModal.value.logs = data.logs;
                    dataModal.value.custom_fields = data.custom_fields;
                })
                .finally(() => {
                    dataModal.value.loading = false;
                });

            jQuery("#" + modal_form_id).show();
        };

        /**
         *
         */
        const closeModalForm = () => {
            jQuery("#" + modal_form_id).hide();
        };

        /**
         * @param selected
         */
        const markCreated = (selected) => {
            closeModalForm();
            if (!props.multiple) {
                selected = selected[0];
            } else {
                var currentSelected = entity.value[theKey.value];
                currentSelected.push({
                    id: selected[0].id,
                    name: selected[0].name,
                });
                selected = currentSelected;
            }
            entity.value[theKey.value] = selected;
            emit("onSetSelectedItems", theKey.value, selected);
        };

        return {
            t,
            /* Data */
            entity,
            modal_id,
            modal_form_id,
            current,
            modal_open,
            reloaded,
            theKey,
            error,
            dataModal,
            /* Methods */
            closeModal,
            openModalGeneral,
            markSelected,
            removeItem,
            getDependences,
            removeFromSelector,
            openModalForm,
            closeModalForm,
            markCreated,
            isEmpty,
            isForCreation,
            showImageFileOrIcon,
        };
    },

};
