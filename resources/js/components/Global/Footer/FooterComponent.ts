import {useI18n} from "vue-i18n";

export default {
    setup() {

        /* Data */
        const {t} = useI18n();

        return {
            /* Data */
            t
        };
    },
};
