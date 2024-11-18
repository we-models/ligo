import {onMounted, ref} from "vue";
import {useI18n} from "vue-i18n";

export default {
    props: ["url_logout","settings_menu"],
    setup(props: any) {

        /* Data */
        const {t} = useI18n();
        const url = ref<string>("");
        const settingsMenu = ref<any>(null);


        /* assign property values */
        onMounted(() => {
            url.value = props.url_logout;
            settingsMenu.value = JSON.parse(props.settings_menu);
        });

        return {
            /* Data */
            t,
            url,
            settingsMenu,
        };
    },
};
