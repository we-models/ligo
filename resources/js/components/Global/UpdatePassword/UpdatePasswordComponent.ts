import { onMounted, ref, nextTick } from "vue";
/* HELPER */
import cts from "@/components/Global/Constants";
import { useI18n } from "vue-i18n";
/* STORE */
import { useAlertStore } from "@/stores/alertStore";

export default {
    props: ["email", "http_method", "csrf", "url"],

    setup(props: any, { emit }) {
        /* Data */
        const email = ref<string>();
        const method = ref<string>("");
        const errorMsg = ref<string>();
        /* I18n */
        const { t } = useI18n();

        /* Store */
        const alertStore = useAlertStore();

        onMounted(() => {
            method.value = props.http_method;

            email.value = props.email?.email;
        });


        /**
         * @param event
         */
        const save = (event: any) => {
            alertStore.success = "";
            alertStore.error = "";

            jQuery.ajax(props.url, {
                method: method.value,
                data: cts.formToJson(event),
                headers: { "X-CSRF-TOKEN": props.csrf },
                xhr: () => {
                    let xhr: XMLHttpRequest = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        // progress.value = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    new Audio(location.origin + "/sounds/success.mp3").play();
                    alertStore.success = t("The password was changed successfully");
                    errorMsg.value = "";
                },
                error: (error) => {
                    new Audio(location.origin + "/sounds/error.ogg").play();

                    nextTick(() => {
                        errorMsg.value = error?.responseJSON?.message;
                    });
                },
                complete: () => {
                    // progress.value = 0;
                },
            });
        };

        return {
            /* Data */
            email,
            errorMsg,

            /* Methods */
            save,
        };
    },
};
