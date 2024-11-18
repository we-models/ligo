import {onMounted, ref} from "vue";
import cts from "@/components/Global/Constants";

/* COMPONENT */
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";
import SearchComponent from "@/components/Global/Search/SearchComponent.vue";
import SearchableComposable from "@/composables/SearchableComposable";
import PaginationComponent from "@/components/Global/Pagination/PaginationComponent.vue";

export default {
    components: {PaginationComponent, SearchComponent, InterfaceLoaderComponent},
    emits: ["onShowDetails"],
    props: [
        "isImage",
        "url",
        "multiple",
        "sorts",
        "quantity",
        "selectable",
        "is_mobile",
        "itemsSelected",
        "required",
    ],
    setup(props: any, {emit}) {
        /* Data */
        const Cts = cts;
        const selected = ref<any>([]);
        const required = ref<boolean>(false);

        const {
            progress, sort, sort_direction,
            pagination, page,
            onList, onProgress, onResetPage, changePage
        } = SearchableComposable();

        onMounted(() => {
            if (
                props.itemsSelected === null ||
                props.itemsSelected === undefined
            )
                return;
            if (Array.isArray(props.itemsSelected))
                selected.value = [].concat(props.itemsSelected);
            else selected.value = [props.itemsSelected];
        });

        /**
         * @param file
         */
        const getImageFromFile = (file: any) => {
            if (file.images.length > 0) {
                return file.images[0].url;
            }
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

        /**
         * @param item
         */
        const setSelection = (item: any) => {
            if (!props.selectable) return;
            item.selected = !item.selected;
            if (!props.multiple) {
                pagination.value.data = pagination.value.data.map((i) => {
                    if (i.id !== item.id) i.selected = false;
                    return i;
                });
                selected.value = item.selected ? [item] : [];
                return;
            }
            if (item.selected) selected.value.push(item);
            else
                selected.value = selected.value.filter((i) => i.id !== item.id);
        };

        /**
         * @param item
         */
        const showDetails = (item: any) => {
            emit("onShowDetails", item);
        };


        return {
            /* Data */
            Cts,
            pagination,
            sort,
            sort_direction,
            selected,
            required,
            progress,
            page,
            onList,
            onProgress,
            onResetPage,
            changePage,
            /* Methods */
            getImageFromFile,
            setSelection,
            showDetails,
        };
    },
};
