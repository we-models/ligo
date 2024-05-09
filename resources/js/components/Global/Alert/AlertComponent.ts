import {getCurrentInstance, ref, watch} from "vue";

import {useAlertStore} from "@/stores/alertStore";

export default {
    props: [],
    name: "AlertComponent",
    setup(props: any) {

        /* Store */
        const alertStore = useAlertStore();

        /**
         * globalProperties
         */
        const instance = getCurrentInstance();

        /* Data */
        const collapse = ref<"down" | "up">("down");

        /**
         * If there is an alert we show it for 3 seconds
         */
        watch(
            () => alertStore.success,
            (success) => {
                if (success !== "") {
                    setTimeout(() => {
                        alertStore.success = "";
                    }, 2000);
                }
            }
        );
        watch(
            () => alertStore.error,
            (error) => {
                if (error !== "") {
                    setTimeout(() => {
                        alertStore.error = "";
                    }, 5000);
                }
            }
        );

        /* Methods */
        const changeCollapse = (): void => {
            collapse.value = collapse.value === "down" ? "up" : "down";
        };
        const cleanMessage = (type = 'error'): void => {
            if (type === 'error') {
                alertStore.error = "";
            }else{
                alertStore.success = "";
            }
        };

        return {
            /** Data */
            alertStore,
            collapse,
            /** Methods */
            changeCollapse,
            cleanMessage,
        };
    },
};
