import {onMounted, onUnmounted, ref} from "vue";

/* COMPONENTS */
import LogsComponent from "@/components/Global/Log/LogsComponent.vue";
import {useI18n} from "vue-i18n";
import {useSizeStore} from "@/stores/SizeStore";
import FormObjectComponent from "@/components/Global/FormObject/FormObjectComponent.vue";

export default {
    components: {FormObjectComponent, LogsComponent},
    props: [
        "object",
        "csrf",
        "title",
        "fields",
        "icons",
        "object_",
        "all",
        "lngs",
        "lng",
        "create",
        "permissions",
        "index",
        "logs",
        "custom_fields",
        "isObject"

    ],
    setup(props: any) {

        const sizeStore = useSizeStore();

        /* Data */
        const selected = ref<number[] | string>(
            props.permissions.length > 0 ? [0] : ""
        );
        const is_mobile = ref<boolean>(false);

        const {t} = useI18n();

        /* Lifecycle hoocks */
        onMounted(() => {
            getDimensions();
            window.addEventListener("resize", getDimensions);
            let tabs = document.querySelector(
                ".all_tabs li button"
            ) as HTMLElement;
            if (tabs != null) tabs.click();
        });

        onUnmounted(() => {
            window.removeEventListener("resize", getDimensions);
        });

        /**
         * @param permission
         */
        const setSelected = (permission: string): void => {
            selected.value = permission;
        };

        const getDimensions = (): void => {
            if (document.documentElement.clientWidth != null) {
                is_mobile.value = document.documentElement.clientWidth <= 768;
            }
            sizeStore.height = window.innerHeight;
            sizeStore.width = window.innerWidth;
        };

        return {
            t,
            /* Data */
            selected,
            is_mobile,
            /* Methods */
            setSelected,
        };
    },
};
