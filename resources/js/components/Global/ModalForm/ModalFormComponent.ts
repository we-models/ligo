import {onMounted, ref} from "vue";
import {trans} from "laravel-vue-i18n";
import EventBus from "@/configurations/eventBus";

/* TYPES */
import type {ModalFormIconType} from "@/types/global/internal/ModalFormType";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";

import { useI18n } from "vue-i18n";


export default {
    name: "ModalFormComponent",
    components: {InterfaceLoaderComponent},

    setup() {
        /* Data */
        const object_ = ref<object | null>(null);
        const fields = ref<object | null>(null);
        const url = ref<string>("");
        const icons = ref<ModalFormIconType[]>([]);
        const csrf = ref<string>("");
        const title = ref<string>("");
        const loading = ref<boolean>(false);
        const error = ref<string | null>(null);
        const method = ref<string | null>(null);
        const custom_fields = ref<any[]>([]);
        /* useI18n */
        const { t } = useI18n();
        const prefix = ref<string>('');



        /* Lifecycle hoocks */
        onMounted(() => {
            EventBus.on("fillModalCRUD", (params) => {

                prefix.value = params["method"]
                switch (params["method"]) {
                    case "SHOW":
                        prefix.value = t('Show');
                        break;

                    case "PUT":
                        prefix.value = t('Update');
                        break;
                    default:
                        prefix.value = t('create');
                        break;
                }


                jQuery("#formEditModal").show();
                let uri_separator = params["index"].split("?");
                params["index"] = uri_separator[0];
                let uri: string =
                    params["index"] +
                    "/" +
                    params["id"] +
                    (params["method"] === "PUT" ? "/edit" : "");
                if (uri_separator.length > 1) {
                    uri += `?${uri_separator[1]}`;
                }
                method.value = params["method"];
                loading.value = true;
                error.value = null;

                fetch(uri)
                    .then((response) => response.json())
                    .then((data) => {
                        object_.value = data.object;
                        if (object_.value == null) {
                            error.value = trans(
                                "This element doesnt have some fields"
                            );
                        } else {
                            fields.value = data.fields;
                            url.value = data.url;
                            csrf.value = data.csrf;
                            icons.value = data.icons;
                            title.value = data.title;
                            custom_fields.value =
                                data.custom_fields !== undefined
                                    ? data.custom_fields
                                    : [];
                        }
                    })
                    .catch((e) => {
                        error.value = t(
                            "Can not get the data for this element"
                        );
                        object_.value = null;
                        fields.value = null;
                    })
                    .finally(() => {
                        loading.value = false;
                    });


            });
        });


        const closeModal = (): void => {
            jQuery("#formEditModal").hide();
            object_.value = null;
        };

        return {
            /* Data */
            object_,
            fields,
            url,
            icons,
            csrf,
            title,
            loading,
            error,
            method,
            custom_fields,
            prefix,
            /* Methods */
            closeModal
        };
    },

};
