import {ref} from "vue";
import cts from "../Constants";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";
import SearchComponent from "@/components/Global/Search/SearchComponent.vue";
import PaginationComponent from "@/components/Global/Pagination/PaginationComponent.vue";
import SearchableComposable from "@/composables/SearchableComposable";
import {useSizeStore} from "@/stores/SizeStore";


export default {
    name: "LogsComponent",
    components: {PaginationComponent, SearchComponent, InterfaceLoaderComponent},
    props: ["url"],

    setup(props: any) {
        /* Data */
        const Cts: any = cts;
        const current_link = ref<string>(props.url);

        /* Store */
        const sizeStore = useSizeStore();


        const {
            progress, sort, sort_direction,
            pagination, page,
            onList, onProgress, onResetPage, changePage
        } = SearchableComposable();

        return {
            /* Data */
            Cts,
            page,
            pagination,
            progress,
            current_link,
            onList,
            onProgress,
            onResetPage,
            changePage,
            /* Store */
            sizeStore
        };
    },
};
