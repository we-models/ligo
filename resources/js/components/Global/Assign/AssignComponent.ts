import {watch, onMounted, ref, onUnmounted, nextTick} from "vue";
import cts from "@/components/Global/Constants";

/* COMPONENTS */
import ProgressComponent from "@/components/Global/Progress/ProgressComponent.vue";

/* TYPES */
import type {
    AssignRelationType,
    AssignRowType,
} from "@/types/global/internal/AssignType";
import SearchComponent from "@/components/Global/Search/SearchComponent.vue";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";

import { useI18n } from "vue-i18n";
import PaginationComponent from "@/components/Global/Pagination/PaginationComponent.vue";
import SearchableComposable from "@/composables/SearchableComposable";
import { useSizeStore } from "@/stores/SizeStore";
import AlertComponent from "@/components/Global/Alert/AlertComponent.vue";
import {useAlertStore} from "@/stores/alertStore";

export default {
    name: "AssignComponent.vue",
    components: {
        AlertComponent,
        PaginationComponent,
        InterfaceLoaderComponent,
        SearchComponent,
        ProgressComponent,
    },
    props: [
        "columns",
        "rows",
        "the_key",
        "url",
        "url_to_save",
        "csrf",
        "lngs",
        "lng",
        "unique",
        "general",
    ],

    setup(props: any) {
        /* Store */
        const sizeStore = useSizeStore();

        /* Data */
        const Cts = cts;
        const link_list = ref<string>("");
        const { t } = useI18n();
        const dataPermissions = ref<any>({});
        const idSelectedEntity = ref<number>(0);
        const isMobile = ref<boolean>(false);
        const iconAssign = ref<string>("");
        const refreshPagination = ref<boolean>(false);
        /* showCards prevents rendering of the cards section when saving. */
        const showCards = ref<boolean>(false);
        const alertStore = useAlertStore();

        const {
            progress,
            pagination,
            page,
            onList,
            onProgress,
            onResetPage,
            changePage,
        } = SearchableComposable();

        onMounted(() => {
            getDimensions();
            window.addEventListener("resize", getDimensions);
            link_list.value = props.url;
            link_list.value = `${link_list.value}/?x=${props.rows}&y=${props.columns}&key=${props.the_key}&general=${props.general}`;
        });
        onUnmounted(() => {
            window.removeEventListener("resize", getDimensions);
        });

        const getDimensions = (): void => {
            sizeStore.height = window.innerHeight;
            sizeStore.width = window.innerWidth;
        };

        watch(
            () => pagination.value,
            (newValue, oldValue) => {
                /*
                * Activates the border property to the first element of the list
                when loading the component for the first time
                */
                const hasKeys = Object.keys(oldValue).length;
                if (hasKeys === 0) {
                    // dataPermissions.value = {};
                    dataPermissions.value = pagination.value.data[0];
                    idSelectedEntity.value = pagination.value.data[0].id;
                    iconAssign.value = pagination.value.iconAssign;
                }
                /*
                 * When paginating or searching, the selected card element is reset
                 */
                if (
                    hasKeys > 0 &&
                    newValue.current_page != oldValue.current_page
                ) {
                    dataPermissions.value = [];
                    idSelectedEntity.value = 0;
                }
                if (hasKeys > 0 && newValue.search !== oldValue.search) {
                    dataPermissions.value = [];
                    idSelectedEntity.value = 0;
                }
            },
            { deep: true }
        );

        /*
         * Hides the collapse of the previous selected element in mobile mode
         */
        watch(
            () => idSelectedEntity.value,
            (newValue, oldValue) => {
                const selectedCard = document.getElementById(
                    `card-${oldValue}`
                );

                // if (newValue != 0) {
                    if (
                        selectedCard != null &&
                        selectedCard.classList.contains("show")
                    ) {
                        selectedCard.classList.remove("show");
                    }
                // }
            },
            { deep: true }
        );

        const formatClassName = (classname: string) => {
            const theClassname = classname.split("\\");
            return t(theClassname[theClassname.length - 1]);
        };

        /**
         * @param row
         * @param relation
         */
        const saveRelation = (
            row: AssignRowType,
            relation: AssignRelationType
        ) => {

            if (progress.value) return;
            showCards.value = true;
            progress.value = true;
            refreshPagination.value = true;
            pagination.value.data = changeRelation(row, relation, false);
            refreshPagination.value = false;
            let jsonForm = {
                x: relation.id,
                y: row.id,
                type: relation.relation,
                key: props.the_key,
            };

            if (props.unique) {
                refreshPagination.value = true;
                changeRelation(row, relation, true);
                refreshPagination.value = false;
            }

            jQuery.ajax(props.url_to_save, {
                method: "POST",
                data: jsonForm,
                headers: { "X-CSRF-TOKEN": props.csrf },
                success: (_response) => {
                    new Audio(location.origin + "/sounds/success.mp3").play();
                    alertStore.success = "";
                    setTimeout(() => {
                        alertStore.success = t("Assignment completed successfully");
                    }, 50);
                },
                error: (_error) => {
                    new Audio(location.origin + "/sounds/error.ogg").play();
                    refreshPagination.value = true;
                    pagination.value.data = changeRelation(
                        row,
                        relation,
                        false
                    );
                    refreshPagination.value = false;
                    console.log("The error",_error)
                    nextTick(() => {
                        alertStore.error = t(_error.responseText);
                    });
                },
                complete: () => {
                    progress.value = false;
                    showCards.value = false;
                },
            });
        };

        /**
         * @param row
         * @param relation
         * @param clean
         * @return AssignRowType[]
         */
        const changeRelation = (
            row: AssignRowType,
            relation: AssignRelationType,
            clean: boolean
        ): AssignRowType[] => {
            let result = pagination.value.data.map((r) => {
                if (r.id === row.id) {
                    if (clean) {
                        r.relations = r.relations.map((c) => {
                            c.relation = false;
                            return c;
                        });
                    }
                    r.relations = r.relations.map((c) => {
                        if (c.id === relation.id) c.relation = !c.relation;
                        return c;
                    });
                }
                return r;
            });
            return result;
        };

        const selectedEntity = (entity) => {
            dataPermissions.value = entity;
            idSelectedEntity.value = entity.id;
        };

        return {
            /* Store */
            sizeStore,
            /* Data */
            Cts,
            pagination,
            progress,
            link_list,
            page,
            dataPermissions,
            idSelectedEntity,
            isMobile,
            iconAssign,
            refreshPagination,
            showCards,
            /* Methods */
            saveRelation,
            changeRelation,
            changePage,
            onList,
            onProgress,
            onResetPage,
            formatClassName,
            selectedEntity,
        };
    },
};
