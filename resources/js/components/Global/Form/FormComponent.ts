import {getCurrentInstance, nextTick, onMounted, onUnmounted, onUpdated, ref, watchEffect} from "vue";
import {trans} from "laravel-vue-i18n";
import cts from "@/components/Global/Constants";

/* COMPONENTS */
import ProgressComponent from "@/components/Global/Progress/ProgressComponent.vue";
import ObjectSelectorComponent from "@/components/Global/ObjectSelector/ObjectSelectorComponent.vue";
import FieldComponent from "@/components/Global/Field/FieldComponent.vue";
import {Collapse} from "bootstrap";
import type {FieldAttributesType} from "@/types/global/internal/FormType";
import {useI18n} from "vue-i18n";
/* STORE */
import {useAlertStore} from "@/stores/alertStore";
import { useGlobalStore } from "@/stores/globalStore";
import {useSizeStore} from "@/stores/SizeStore";

export default {
    components: {
        FieldComponent,
        ObjectSelectorComponent,
        ProgressComponent,
    },
    emits: ["onSelected"],
    props: [
        "object",
        "fields",
        "icons",
        "csrf",
        "title",
        "url",
        "http_method",
        "custom_fields",
        "prefix"
    ],
    setup(props: any, {emit}) {
        /**
         * GlobalProperties
         */
        const instance = getCurrentInstance();

        const {t} = useI18n();

        /* Store */
        const alertStore = useAlertStore();
        const globalStore = useGlobalStore();
        const FormComponentRef = ref(null);
        const loaded = ref<boolean>(false)


        /* Data */
        const Cts = cts;
        const entity = ref<any>({});
        const all_fields = ref<any>({});
        const progress = ref<number>(0);
        const method = ref<string>("");
        const isReadOnly = ref<boolean>(false);
        const fieldsChangeRamdomString = ref<any>([]);
        const formWidth = ref<number>(0);

        const sizeStore = useSizeStore();

        /**
         * inicializar variables
         */
        onMounted(() => {
            entity.value = props.object;
            all_fields.value = props.fields;
            method.value = props.http_method;
            isReadOnly.value = ["SHOW", "DELETE"].includes(props.http_method);

            mapGenerateRandomStringForFields();
            getFormWidth();
            window.addEventListener('resize', getFormWidth)
            setTimeout(()=>{
                loaded.value = true;
            }, 100)
        });

        onUnmounted(()=>{
            window.removeEventListener("resize", getFormWidth);
        })

        const getFormWidth = ()=>{
            nextTick(()=>{
                const formComponentElement = FormComponentRef.value;
                const theWidth = formComponentElement.clientWidth;
                if(theWidth > 0){
                    formWidth.value = theWidth;
                }
            })
        }

        /*
         * Maps in a variable called fieldsChangeRamdomString the fields that the ramdom_string has to generate and in what methods it can do it.
         */
        const mapGenerateRandomStringForFields = (): void => {
            for (let field in all_fields.value) {
                checkGenerateRandomString(field, all_fields.value[field]);
            }
        };

        /**
         * @param key
         * @param objeto
         * Function to check if a property has the condition "generate_random_string" to true and if so, map the variable fieldsChangeRamdomString
         */
        const checkGenerateRandomString = (key: string, objeto): void => {
            if (
                objeto.attributes &&
                objeto.attributes.conditions &&
                objeto.attributes.conditions.length > 0
            ) {
                const conditions = hasGenerateRandomStringTrue(
                    objeto.attributes.conditions
                );
                if (conditions !== undefined) {
                    fieldsChangeRamdomString.value.push({
                        key,
                        methods: conditions.methods,
                    });
                }
            }
        };

        /**
         * @return object
         * Function to check and return the object if a property has the condition "generate_random_string" true
         */
        const hasGenerateRandomStringTrue = (conditions) => {
            return conditions.find(
                (condition) => condition.generate_random_string === true
            );
        };

        /**
         * Function to apply the random string to the fields that have the condition "generate_random_string" to true
         */
        const applyStringRandom = (): void => {
            if (fieldsChangeRamdomString.value.length > 0) {
                fieldsChangeRamdomString.value.forEach((field) => {
                    if (
                        entity.value.hasOwnProperty(field.key) &&
                        field.methods.includes(method.value)
                    ) {
                        entity.value[field.key] = generateRandomString(32);
                    }
                });
            }
        };

        /**
         *
         * @param length
         * @return  string
         * Function to generate a random string
         */
        const generateRandomString = (length: number = 8) => {
            const characters: string =
                "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            const charactersLength: number = characters.length;
            let randomString: string = "";
            for (let i: number = 0; i < length; i++) {
                randomString += characters.charAt(
                    Math.floor(Math.random() * charactersLength)
                );
            }
            return randomString;
        };

        /**
         * @param event
         */
        const save = (event: any) => {
            if (isReadOnly.value) return;
            alertStore.success = '';
            alertStore.error = '';

            jQuery.ajax(props.url, {
                method: method.value,
                data: cts.formToJson(event),
                headers: {"X-CSRF-TOKEN": props.csrf},
                xhr: () => {
                    let xhr: XMLHttpRequest = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        progress.value = Math.round((e.loaded / e.total) * 98);
                    };
                    return xhr;
                },
                success: (_response) => {
                    new Audio(location.origin + "/sounds/success.mp3").play();
                    alertStore.success = t("The data was saved successfully");

                    /* If you have data in the response object you can replace x values when saving or editing */
                    if (typeof _response === 'object' && _response.hasOwnProperty('data')) {
                        entity.value.name = _response.data.name;
                    }

                    if (_response[1] !== undefined && typeof _response[1] === 'object') {
                        let data = _response[1];
                        let value = {id: data.id, name: data.name};
                        let selected = [value];
                        emit("onSelected", selected);
                    }
                    applyStringRandom();
                },
                error: (error) => {
                    new Audio(location.origin + "/sounds/error.ogg").play();

                    nextTick(() => {
                        alertStore.error = error.responseText;
                    });
                },
                complete: () => {
                    progress.value = 0;
                },
            });
        };

        /**
         * @param k
         * @param s
         */
        const markFieldSelected = (k: string, s: any): void => {
            entity.value[k] = s;
        };
        /**
         * @param fields
         * @return boolean
         */
        const getReadOnly = (fields: any): boolean => {
            if (fields.attributes.hasOwnProperty("readonly")) {
                return fields.attributes.readonly;
            }
            return false;
        };

         /**
         * @param fields
         * @return boolean
         */
         const getReadOnlyCustomFields = (fields: any): boolean => {
            if (fields.data.hasOwnProperty("readonly")) {
                return fields.data.readonly;
            }
            return false;
        };
        /**
         * @param value
         * @return string
         */

        const getColumnWidth = (value: any): string => {

            const part = Math.floor((formWidth.value -50) / 12);

            if(typeof value == 'number') {
                const val = (part * value) -15;
                if(sizeStore.isMobile) return 'width:100%;display:unset'

                return `width: ${val}px; max-width: ${val}px; min-width: 200px;`;
            }

            if (value.hasOwnProperty("properties") &&  value.properties.width < 12) {
                const val = (part * value.properties.width) -15;
                return `width: ${val}px; max-width: ${val}px; min-width: 200px;`;
            }else{
                return "width:100%";
            }
        };

        const getColumnWidthForTab = (value: number, tab:any): string => {
            if(sizeStore.isTablet) return 'width:100%'
            let tabElement = document.getElementById('cf_collapse_' + tab);
            if(tabElement != null && tabElement.clientWidth > 50 ){
                const part = Math.floor((tabElement.clientWidth -50) / 12);

                const val = (part * value) -15;
                console.log(tabElement.clientWidth, part, value, val)
                return `max-width: ${val}px; min-width: 200px;`;
            }
            return 'width:100%';
        };

        const isRelationOrMediaFile = (field: any) => {
            return field.status === 'relation' || (field.layout !== 'tab' && (['Image', 'File'].includes(field.type.name)))
        };

        const isField = (field:any)=>{
            return field.layout === 'field' && !['Image', 'File'].includes(field.type.name);
        }

        const cleaForm = ()=>{
            for (let key in entity.value) {
                if (entity.value.hasOwnProperty(key)) {
                    if(typeof  entity.value[key] == 'object'){
                        entity.value[key] = null;
                    }else if(typeof  entity.value[key] == 'number'){
                        entity.value[key] = 0;
                    }else{
                        entity.value[key] = "";
                    }
                }
            }
            applyStringRandom();
        }

        /**
         * @param v
         * @param k
         * @return boolean
         */
        const getCondition = (v: any, k: any): boolean => {
            let response: boolean = true;
            if (v.hasOwnProperty("conditions")) {
                for (let i = 0; i < v.conditions.length; i++) {
                    let theValue = v.conditions[i].value;

                    let the_field = entity.value[v.conditions[i].field];
                    try {
                        the_field =
                            "id" in the_field ? the_field.id : the_field;
                    } catch (error) {
                    }

                    if (v.conditions[i].operation === "=") {
                        if (the_field != theValue) {
                            response = false;
                        }
                    }

                    if (v.conditions[i].operation === "!=") {
                        if (the_field == theValue) {
                            response = false;
                        }
                    }
                }
            }
            return response;
        };

        /**
         * @param v
         */
        const optionsShowSelect = (v: any) => {
            if (v.hasOwnProperty("conditions") && v.conditions.length > 0) {
                for (let i = 0; i < v.conditions.length; i++) {
                    let theValue = v.conditions[i].value;

                    let the_field = entity.value[v.conditions[i].field];

                    try {
                        the_field =
                            "id" in the_field ? the_field.id : the_field;
                    } catch (error) {}

                    if (the_field == theValue) {
                        return v.conditions[i].show_only;
                    }
                }
            }
            return v.options;
        };

        /**
         * @param value
         * @return object
         */
        const getBindAttributes = (value: any): object => {
            let response: object = {};
            Object.keys(value).forEach((key) => {
                if (key !== "type" && key !== "options") {
                    response[key] = value[key];
                }
            });
            return response;
        };

        /**
         * @param value
         * @return any[]
         */

        const getDepends = (value: any): any[] => {
            let response = [];
            if (
                value.attributes.depends !== undefined &&
                value.attributes.depends != null
            ) {
                let depends = value.attributes.depends;
                for (let i = 0; i < depends.length; i++) {
                    if (entity.value[depends[i].field] !== null) {
                        response.push(
                            `${depends[i].column}=${
                                entity.value[depends[i].field].id
                            }`
                        );
                    } else {
                        response.push(
                            `${depends[i].field}=${depends[i].column}`
                        );
                    }
                }
            }
            return response;
        };

        /**
         * @param id
         */
        const toggleCF = (id: number) => {
            new Collapse(id, {toggle: true});
        };

        /**
         * @param field
         * @return string
         */
        const getValue = (field: any): string => {
            if (field.value == null) return "";
            return field.value.value;
        };

        /**
         * @param field
         * @return FieldAttributesType
         */
        const fieldAttributes = (field: any): FieldAttributesType => {
            if (field.type.name !== undefined) {
                return {
                    isMultiple: false,
                    object: field.type.name.toLowerCase(),
                };
            } else {
                return {
                    isMultiple: field.type === "multiple",
                    object: "object",
                };
            }
        };

        return {
            /* Data */
            Cts,
            entity,
            all_fields,
            progress,
            method,
            isReadOnly,
            fieldsChangeRamdomString,
            /* Methods */
            save,
            markFieldSelected,
            getReadOnly,
            getColumnWidth,
            getCondition,
            getBindAttributes,
            getDepends,
            toggleCF,
            getValue,
            fieldAttributes,
            isRelationOrMediaFile,
            isField,
            cleaForm,
            optionsShowSelect,
            getReadOnlyCustomFields,
            /* Store */
            globalStore,
            FormComponentRef,
            loaded,
            getColumnWidthForTab
        };
    },
};
