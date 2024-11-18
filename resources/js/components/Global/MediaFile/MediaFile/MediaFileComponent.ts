import { onMounted, onUnmounted, ref } from "vue";

/* Component */
import MediaFileUploadComponent from "@/components/Global/MediaFile/MediaFileUpload/MediaFileUploadComponent.vue";
import MediaFileListComponent from "@/components/Global/MediaFile/MediaFileList/MediaFileListComponent.vue";
import { useSizeStore } from "@/stores/SizeStore";
// import ProgressComponent from "@/components/Global/Progress/ProgressComponent.vue";

export default {
    components: { MediaFileUploadComponent, MediaFileListComponent },

    props: [
        "isImage",
        "post",
        "csrf",
        "multiple",
        "url",
        "multiple",
        "sorts",
        "quantity",
        "selectable",
        "setChosen",
        "itemsSelected",
        "accept",
        "field_id",
        "field_layout",
        "isObjectSelector",
    ],
    setup(props: any) {
        /*
         * Destructure the props object and set a default value for isObjectSelector
         */
        const { isObjectSelector = false } = props;

        /* Data */
        const is_mobile = ref<boolean>(false);
        const paginate = ref<any>();
        const search = ref<string>("");
        const pagination = ref<any>([]);
        const progress_list = ref<boolean>(false);
        const sort = ref<string>("created_at");
        const sort_direction = ref<string>("desc");
        const showed = ref<any>(null);
        const showList = ref<boolean>(false);
        const sizeStore = useSizeStore();

        const microsoft = ref<string>(
            "https://view.officeapps.live.com/op/embed.aspx?src="
        );
        const is_microsoft_file = ref<boolean>(false);

        /*
         * Assign prop values
         */
        onMounted(() => {
            paginate.value = props.quantity;

            getDimensions();
            window.addEventListener("resize", getDimensions);
        });

        onUnmounted(() => {
            window.removeEventListener("resize", getDimensions);
        });

        /**
         *
         */
        const toggleMediaList = () => {
            showList.value = !showList.value;
        };

        /**
         *
         */
        const showMediaModal = (item: any) => {
            showed.value = item;

            if (!props.isImage) is_microsoft();

            jQuery("#mediaModal").show();
        };

        /**
         *
         */
        const closeModal = () => {
            showed.value = null;
            jQuery("#mediaModal").hide();
        };

        /**
         *
         */
        const getDimensions = () => {
            is_mobile.value = document.documentElement.clientWidth <= 768;
            sizeStore.height = window.innerHeight;
            sizeStore.width = window.innerWidth;
        };

        /**
         *
         */
        const is_microsoft = () => {
            const microsoft_formats = [
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "application/vnd.oasis.opendocument.text",
                "application/vnd.openxmlformats-officedocument.presentationml.presentation",
                "application/vnd.oasis.opendocument.presentation",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "application/vnd.oasis.opendocument.spreadsheet",
                "application/msword",
                "application/vnd.ms-excel",
                "application/vnd.ms-powerpoint",
            ];
            is_microsoft_file.value = microsoft_formats.includes(
                showed.value.mimetype
            );
        };

        return {
            /* Data */
            is_mobile,
            paginate,
            search,
            pagination,
            progress_list,
            sort,
            sort_direction,
            showed,
            showList,
            microsoft,
            is_microsoft_file,
            /* Prop Default value */
            isObjectSelector,
            /* Methods */
            is_microsoft,
            toggleMediaList,
            showMediaModal,
            closeModal,
            getDimensions,
        };
    },
};
