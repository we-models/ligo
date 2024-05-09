import { computed, nextTick, onMounted, ref, watch } from "vue";
import cts from "@/components/Global/Constants";
import { trans } from "laravel-vue-i18n";

import Swal from "sweetalert2";
import EventBus from "@/configurations/eventBus";

/* COMPONENTS */
import AlertComponent from "@/components/Global/Alert/AlertComponent.vue";
import ProgressComponent from "@/components/Global/Progress/ProgressComponent.vue";
import ModalFormComponent from "@/components/Global/ModalForm/ModalFormComponent.vue";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";
import SearchComponent from "@/components/Global/Search/SearchComponent.vue";
import SortComponent from "@/components/Global/Sort/SortComponent.vue";
import ListValueComponent from "@/components/Global/ListValue/ListValueComponent.vue";
import PaginationComponent from "@/components/Global/Pagination/PaginationComponent.vue";
import SearchableComposable from "@/composables/SearchableComposable";
import { useSizeStore } from "@/stores/SizeStore";
import { useI18n } from "vue-i18n";
import { useAlertStore } from "@/stores/alertStore";

export default {
    components: {
        PaginationComponent,
        ListValueComponent,
        SortComponent,
        SearchComponent,
        InterfaceLoaderComponent,
        ModalFormComponent,
        ProgressComponent,
        AlertComponent,
    },
    props: [
        "object",
        "fields",
        "url",
        "permissions",
        "index",
        "csrf",
        "multiple",
        "name_choose",
        "onSelected",
        "itemsSelected",
        "req",
        "object_class",
    ],
    name: "ListComponent",

    setup(props: any) {
        /**
         * GlobalProperties
         */

        const { t } = useI18n();

        /* Data */
        const Cts = cts;
        const all_fields = ref<any>();
        const entity = ref<any>();

        const selected = ref<any>();
        const required = ref<any>();
        const progress_value = ref<number>(0);
        const alert_listener = ref<number>();
        const selectedAll = ref<boolean>(false);

        const alertStore = useAlertStore();

        const sizeStore = useSizeStore();

        const {
            progress,
            sort,
            sort_direction,
            pagination,
            page,
            onList,
            onProgress,
            onResetPage,
            changePage,
        } = SearchableComposable();


        /*
         * assign prop values
         */
        onMounted(() => {
            all_fields.value = formatedList(props.fields);
            entity.value = props.object;
            selected.value = getItemsSelected();
            required.value = props.req === undefined ? false : props.req;
        });

        watch(
            () => pagination.value.data,
            () => {
                nextTick(() => {
                    selected.value.forEach((itemSelected) => {
                        setSelectionByRow("inp_" + itemSelected.id);
                    });
                });
            },
            { deep: true }
        );

        const selectedMap  = computed(()=>{
            return selected.value.map((itemSelected) => itemSelected.id);
        });


        const entityResume = computed(()=>{
            if(sizeStore.isMobile){
                return {
                    id : entity.value?.id,
                    name : entity.value?.name,
                    description : entity.value?.description,
                    enable : entity.value?.enable,
                    created_at : entity.value?.created_at
                }
            }else{
                return entity.value;
            }
        });


        const selectedItemClassMobile = (id: number) => {

            if (props.name_choose != null && selectedMap.value.includes(id))
                return "card selected-item-class";

            return "card";
        };

        const selectedItemClassDesk = (id: number) => {

            if (props.name_choose != null && selectedMap.value.includes(id))
                return "selected-item-class";

            return "";
        };

        const searchComponentClass = () =>{
            if (props.name_choose != null && sizeStore.width >= 769 && sizeStore.width<= 1023) {
                if (selected.value != null && selected.value.length > 0) {
                    return 'search-component-div-50'
                }
                return 'search-component-div-100'
            }
        }


        /**
         * @param url
         * @return any[]
         */
        const getItemsSelected = (): any[] => {
            if (props.itemsSelected === undefined) return [];
            if (props.itemsSelected === null) return [];
            if (Array.isArray(props.itemsSelected)) return props.itemsSelected;
            if (!Array.isArray(props.itemsSelected))
                return [props.itemsSelected];
            return [];
        };

        /**
         * @param fs
         */
        const formatedList = (fs: any) => {
            let values = fs;
            Object.keys(values).forEach((c) => {
                values[c].direction = "";
            });
            return values;
        };

        /**
         * @param id
         * @param method
         * @param link
         */
        const methodsLine = (
            id: string,
            method: string,
            link: string
        ): void => {
            EventBus.emit("fillModalCRUD", {
                index: link,
                id: id,
                method: method,
            });
        };

        /**
         * @param item
         */
        const deleteItem = (item: any) => {
            Swal.fire({
                title: t("Are you sure?"),
                text: t("You won't be able to revert this!"),
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "var(--primary)",
                cancelButtonColor: "var(--secondary)",
                confirmButtonText: t("Yes, delete it!"),
                cancelButtonText: t("Cancel"),
            }).then((result) => {
                if (result.isConfirmed) {
                    let theUri = `${props.index}/${item["id"]}`;
                    if (entity.value.object_type !== undefined) {
                        theUri = `${theUri}?object_type=${entity.value.object_type.id}`;
                    }

                    jQuery.ajax(theUri, {
                        method: "DELETE",
                        data: { _token: props.csrf },
                        xhr: () => {
                            let xhr = new XMLHttpRequest();
                            xhr.upload.onprogress = (e) => {
                                progress_value.value = Math.round(
                                    (e.loaded / e.total) * 98
                                );
                            };
                            return xhr;
                        },
                        success: (response) => {
                            Swal.close();
                            alertStore.success = t("item removed successfully");
                            pagination.value.data =
                                pagination.value.data.filter((dt: any) => {
                                    return dt.id !== item.id;
                                });
                        },
                        error: (error) => {
                            alertStore.error = error.responseText;
                        },
                        complete: () => {
                            progress_value.value = 0;
                        },
                    });
                }
            });
        };

        /**
         * @param key
         * @param direction
         */
        const refillDirection = (key: string, direction: string) => {
            let clean = false;
            if (all_fields.value[key].direction === direction) clean = true;
            Object.keys(all_fields.value).forEach((k) => {
                all_fields.value[k].direction = "";
            });
            if (clean) {
                sort.value = sort_direction.value = "";
            } else {
                sort.value = key;
                sort_direction.value = all_fields.value[key].direction =
                    direction;
            }
        };

        /**
         * @param input
         * @param value
         */
        const setSelection = (input: any, value: any) => {
            value = { id: value.id, name: value.name, tag: input };
            if (!props.multiple) {
                selected.value = [value];
                return;
            }
            let checked = false;
            if (
                input.currentTarget !== undefined &&
                input.currentTarget.checked === true
            )
                checked = true;
            if (input.checked === true) checked = true;
            if (checked === true) {
                var existValue = selected.value.some(function (item) {
                    return item.id === value.id;
                });
                if (!existValue) {
                    selected.value.push(value);
                }
            } else {
                selected.value = selected.value.filter(
                    (item) => item.id !== value.id
                );
            }
        };

        /**
         * @param selectedValue
         */
        const removeSelected = (selectedValue: any) => {
            selected.value = selected.value.filter(
                (item) => item.id !== selectedValue.id
            );
            let check = document.querySelector<HTMLInputElement>(
                "#inp_" + selectedValue.id
            );
            check.checked = false;
        };

        /**
         * @param id
         */
        const setSelectionByRow = (id: any) => {
            if (props.name_choose == null) return;
            let el = document.querySelector<HTMLElement>("#" + id);
            if (el != null) {
                el.click();
            }
        };

        /**
         * @param id
         */
        const selectAndSend = (id: any) => {
            if (!props.multiple) {
                setSelectionByRow(id);
                let btnSelector = document.querySelector<HTMLElement>(
                    "#markAsSelectedButton"
                );
                if (btnSelector != null) btnSelector.click();
            }
        };

        /**
         * @param id
         */
        const formatJson = (text: any) => {
            if (text == null) return "";
            return typeof text === "object"
                ? `${text.id} : ${text.name}`
                : text;
        };

        /**
         * @param currentItem
         */
        const checkedVerify = (currentItem: any) => {
            var existValue = selected.value.some(function (item) {
                return item.id === currentItem.id;
            });
            return selectedAll.value && existValue;
        };

        return {
            /* Data */
            Cts,
            page,
            progress,
            all_fields,
            entity,
            pagination,
            sort,
            sort_direction,
            selected,
            required,
            progress_value,
            alert_listener,
            selectedAll,
            sizeStore,
            entityResume,
            /* Methods */
            getItemsSelected,
            formatedList,
            methodsLine,
            deleteItem,
            refillDirection,
            setSelection,
            removeSelected,
            setSelectionByRow,
            selectAndSend,
            formatJson,
            checkedVerify,
            onList,
            onProgress,
            changePage,
            onResetPage,
            selectedItemClassMobile,
            selectedItemClassDesk,
            searchComponentClass
        };
    },
};
