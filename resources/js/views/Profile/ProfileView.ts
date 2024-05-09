import { onMounted, ref ,onUnmounted} from "vue";
import { useI18n } from "vue-i18n";

/* COMPONENTS */
import UpdatePasswordComponent from "@/components/Global/UpdatePassword/UpdatePasswordComponent.vue";
import {useSizeStore} from "@/stores/SizeStore";

export default {
    name: "ProfileView",
    components: {UpdatePasswordComponent},
    props: ["fields", "icons", "csrf", "url_update_profile","url_update_password", "title", "object"],

    setup(props: any) {
        /* Data */
        const selected = ref<string>('.update.profile');

        /* I18n */
        const { t } = useI18n();

        /* Store */
        const sizeStore = useSizeStore();


        onMounted(() => {
            getDimensions();
            window.addEventListener("resize", getDimensions);
        });

        onUnmounted(() => {
            window.removeEventListener("resize", getDimensions);
        });

        const getDimensions = (): void => {
            sizeStore.height = window.innerHeight;
            sizeStore.width = window.innerWidth;
        };

        /**
         * @param permission
         */
        const setSelected = (selectedValue: string): void => {

            selected.value = selectedValue;
        };

        return {
            /* Data */
            selected,
            /* I18n */
            t,
            /* Store */
            sizeStore,
            /* Methods */
            setSelected
        };
    },
};
