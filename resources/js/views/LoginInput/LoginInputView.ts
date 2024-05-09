import {computed, onMounted, ref} from "vue";
import {useI18n} from "vue-i18n";

export default {
    props: [
        "old_value_email",
        "show_two_password_input",
        "name_password_two",
        "placeholder_password_one",
        "placeholder_password_two",
        "styles"
    ],
    setup(props: any) {
        /* Data */
        const {t} = useI18n();
        const value_email = ref<string>("");

        const typeInput1 = ref<string>("password");
        const typeInput2 = ref<string>("password");

        const placeholderPass1 = ref<string>("password");
        const placeholderPass2 = ref<string>("password");
        const isActive  = ref<boolean>(false);
        /**
         * calculate which icon eye to use
         */
        const iconEyeClass1 = computed<string>(() => {
            return typeInput1.value === "password"
                ? "fa-regular fa-eye"
                : "fa-solid fa-eye";
        });

        const iconEyeClass2 = computed<string>(() => {
            return typeInput2.value === "password"
                ? "fa-regular fa-eye"
                : "fa-solid fa-eye";
        });

        /**
         * Assign props values to variable
         */
        onMounted(() => {
            if (props.placeholder_password_one !== undefined)
                placeholderPass1.value = props.placeholder_password_one;

            if (props.placeholder_password_two !== undefined)
                placeholderPass2.value = props.placeholder_password_two;

            value_email.value = props.old_value_email;

            isActive.value = props.styles ? true : false;

        });

        /**
         * change the type of input, to show or hide the password
         */
        const showPassword1 = () => {
            if (typeInput1.value === "password") typeInput1.value = "text";
            else typeInput1.value = "password";
        };
        const showPassword2 = () => {
            if (typeInput2.value === "password") typeInput2.value = "text";
            else typeInput2.value = "password";
        };

        return {
            /* Data */
            t,
            value_email,
            typeInput1,
            typeInput2,
            placeholderPass1,
            placeholderPass2,
            isActive,

            /* Computed */
            iconEyeClass1,
            iconEyeClass2,

            /* Methods */
            showPassword1,
            showPassword2,
        };
    },
};
