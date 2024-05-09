import {onMounted, ref} from "vue";

/* COMPONENTS */
import CrudComponent from "@/components/Global/Crud/CrudComponent.vue";
import InterfaceLoaderComponent from "@/components/Global/InterfaceLoader/InterfaceLoaderComponent.vue";

export default {
    name: "GlobalView",
    components: {InterfaceLoaderComponent, CrudComponent},
    props: ["details", "isObject"],

    setup(props: any) {
        /* Data */
        const object = ref<string>("");
        const title = ref<string>("");
        const csrf = ref<string>("");
        const fields = ref<any>([]);
        const icons = ref<any>([]);
        const values = ref<any>({});
        const index = ref<string>("");
        const all = ref<string>("");
        const create = ref<string>("");
        const languages = ref<any>([]);
        const language = ref<string>("en");
        const permissions = ref<any>([]);
        const logs = ref<string>("");
        const custom_fields = ref<any>([]);
        const loaded = ref<boolean>(false);

        onMounted(() => {
            fetch(props.details)
                .then((response) => response.json())
                .then((data) => {
                    object.value = data.object;
                    title.value = data.title;
                    csrf.value = data.csrf;
                    fields.value = data.fields;
                    icons.value = data.icons;
                    values.value = data.values;
                    index.value = data.index;
                    all.value = data.all;
                    create.value = data.create;
                    languages.value = data.languages;
                    language.value = data.language;
                    permissions.value = data.permissions;
                    logs.value = data.logs;
                    custom_fields.value = data.custom_fields;
                })
                .finally(() => {
                    loaded.value = true;
                });
        });

        return {
            object,
            title,
            csrf,
            fields,
            icons,
            values,
            index,
            all,
            create,
            languages,
            language,
            permissions,
            logs,
            custom_fields,
            loaded,
        };
    },
};
