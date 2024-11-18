import {computed, nextTick, onMounted, ref} from "vue";
import TheObject from "@/models/ObjectModels/TheObject";

import AlertComponent from "@/components/Global/Alert/AlertComponent.vue";
import ProgressComponent from "@/components/Global/Progress/ProgressComponent.vue";
import ObjectSelectorComponent from "@/components/Global/ObjectSelector/ObjectSelectorComponent.vue";
import FieldComponent from "@/components/Global/Field/FieldComponent.vue";

import {useSizeStore} from "@/stores/SizeStore";
import cts from "@/components/Global/Constants";


export default {
    components: {FieldComponent, ObjectSelectorComponent, ProgressComponent, AlertComponent},
    props: ['csrf', 'fields', 'custom_fields', 'object', 'title', 'url', 'prefix', 'http_method'],
    name: "FormObjectComponent",
    setup(props: any, ctx: any) {

        const currentObject = ref<TheObject | null>(null);
        const isReadOnly = ref<boolean>(false);
        const progress = ref<number>(0);
        const formWidth = ref<number>(0);
        const FormComponentRef = ref(null);
        const sizeStore = useSizeStore();
        const Cts = cts;

        onMounted(() => {
            currentObject.value = TheObject.FromJSON(props.custom_fields);
            isReadOnly.value = ["SHOW", "DELETE"].includes(props.http_method);
            getFormWidth();
        });


        const isNameNoEditable = computed(() => {
            return currentObject.value.object_type !== undefined && !currentObject.value.object_type.editable_name;
        })

        const getFormWidth = () => {
            nextTick(() => {
                const formComponentElement = FormComponentRef.value;
                const theWidth = formComponentElement.clientWidth;
                if (theWidth > 0) {
                    formWidth.value = theWidth;
                }
            })
        }

        const getReadOnly = (fields: any): boolean => {
            if (fields.attributes.hasOwnProperty("readonly")) {
                return fields.attributes.readonly;
            }
            return false;
        };

        const save = () => {

        }

        const getColumnWidth = (value: any): string => {
            const part = Math.floor((formWidth.value - 50) / 12);
            if (typeof value == 'number') {
                const val = (part * value) - 15;
                if (sizeStore.isMobile) return 'width:100%;display:unset'
                return `width: ${val}px; max-width: ${val}px; min-width: 200px;`;
            }
            if (value.hasOwnProperty("properties") && value.properties.width < 12) {
                const val = (part * value.properties.width) - 15;
                return `width: ${val}px; max-width: ${val}px; min-width: 200px;`;
            } else {
                return "width:100%";
            }
        };

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

                    let the_field = currentObject.value[v.conditions[i].field];
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
         * @param k
         * @param s
         * @param metavalue
         */
        const markFieldSelected = (k: string, s: any, metavalue:any): void => {
            console.log("Selector: ",k, s, metavalue);
            console.log(currentObject.value[k]);
            currentObject.value[k] = s;
            console.log(currentObject.value[k]);
        };

        const getDepends = (value: any): any[] => {
            let response = [];
            if (
                value.attributes.depends !== undefined &&
                value.attributes.depends != null
            ) {
                let depends = value.attributes.depends;
                for (let i = 0; i < depends.length; i++) {
                    if (currentObject.value[depends[i].field] !== null) {
                        response.push(
                            `${depends[i].column}=${
                                currentObject.value[depends[i].field].id
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
         * @param v
         */
        const optionsShowSelect = (v: any) => {
            if (v.hasOwnProperty("conditions") && v.conditions.length > 0) {
                for (let i = 0; i < v.conditions.length; i++) {
                    let theValue = v.conditions[i].value;
                    let the_field = currentObject.value[v.conditions[i].field];
                    try {
                        the_field = "id" in the_field ? the_field.id : the_field;
                    } catch (error) {
                    }
                    if (the_field == theValue) {
                        return v.conditions[i].show_only;
                    }
                }
            }
            return v.options;
        };

        return {
            Cts,
            FormComponentRef,
            currentObject,
            isReadOnly,
            progress,
            isNameNoEditable,
            save,
            getColumnWidth,
            getCondition,
            getReadOnly,
            markFieldSelected,
            getDepends,
            getBindAttributes,
            optionsShowSelect
        }
    }
}
