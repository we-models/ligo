import {onMounted, onUnmounted, ref} from "vue";
import cts from "@/components/Global/Constants";

/* COMPONENTS */
/* TYPES */
import type {
    SystemConfigDataInformationType,
    SystemConfigListOfTypesType,
    SystemConfigResponseType,
} from "@/types/global/internal/SystemConfigType";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";
import {useSizeStore} from "@/stores/SizeStore";

export default {
    components: {InterfaceLoaderComponent},
    props: [
        "csrf",
        "title",
        "all",
        "lngs",
        "lng",
        "create",
        "index",
        "types",
        "store",
    ],
    name: "SystemConfigComponent",

    setup(props: any) {
        /* Data */
        const Cts = cts;
        const list_types = ref<SystemConfigListOfTypesType[]>([]);
        const lang = ref<string>("");
        const langs = ref<string>("");
        const selected = ref<any | null>(null);
        const pagination = ref<any>({});
        const first = ref<any | null>(null);
        const showTitle = ref<boolean>(false);
        const loading = ref<boolean>(false);

        const sizeStore = useSizeStore();

        onMounted(() => {
            list_types.value = JSON.parse(props.types);
            lang.value = props.lng;
            langs.value = props.lngs;

            setTypeSelected(list_types.value[0]);
            getDimensions();
            window.addEventListener("resize", getDimensions);
        });
        onUnmounted(() => {
            window.removeEventListener("resize", getDimensions);
        });

        /**
         * @param type
         */
        const setTypeSelected = (type: SystemConfigListOfTypesType): void => {
            loading.value = true;
            selected.value = type;
            let uri: string = props.all + "?type=" + selected.value.id;
            getValuesFromLink(uri);
        };
        /**
         * @param uri
         */
        const getValuesFromLink = (uri: string): void => {
            loading.value = true;
            uri = uri + "&type=" + selected.value.id;
            fetch(uri)
                .then((response) => response.json())
                .then((data) => {
                    pagination.value = data;
                    first.value = data.data[0] ?? null;
                    for (let i = 0; i < pagination.value.data.length; i++) {
                        if (pagination.value.data[i].configuration == null) {
                            pagination.value.data[i].configuration = {
                                value: "",
                                exists: false,
                            };
                        } else {
                            pagination.value.data[i].configuration.exists = true;
                        }
                        pagination.value.data[i].progress = 0;
                    }
                })
                .finally(() => {
                    loading.value = false;
                });
        };

        const getDimensions = (): void => {
            showTitle.value = document.documentElement.clientWidth <= 768;
            sizeStore.height = window.innerHeight;
            sizeStore.width = window.innerWidth;
        };

        /**
         * @param item
         * @return SystemConfigResponseType
         */
        const getTypeAttributes = (
            item: SystemConfigDataInformationType
        ): SystemConfigResponseType => {
            let vTypes = Cts.value_types;
            let response: SystemConfigResponseType = {
                placeholder: item.default ?? "",
                value:
                    item.configuration != null ? item.configuration.value : "",
                type: vTypes[item.type.name].type,
                step: null,
            };
            // response.type = vTypes[item.type.name].type;
            if (vTypes[item.type.name].step !== undefined)
                response.step = vTypes[item.type.name].step;
            return response;
        };
        /**
         * @param item
         */
        const saveConfiguration = (item: SystemConfigDataInformationType): void => {
            item.progress = 0;
            jQuery.ajax(props.store, {
                method: "POST",
                data: item,
                headers: {"X-CSRF-TOKEN": props.csrf},
                xhr: (): XMLHttpRequest => {
                    let xhr: XMLHttpRequest = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        item.progress = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    item.configuration.exists = true;
                    item.configuration.id = _response.configuration;
                },
                error: (_error) => {
                },
                complete: () => {
                    item.progress = 0;
                },
            });
        };

        return {
            /* Data */
            Cts,
            list_types,
            lang,
            langs,
            selected,
            pagination,
            first,
            showTitle,
            loading,
            sizeStore,
            /* Methods */
            setTypeSelected,
            getValuesFromLink,
            getDimensions,
            getTypeAttributes,
            saveConfiguration
        }
    },
};
