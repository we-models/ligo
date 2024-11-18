import {computed} from "vue";
import {useI18n} from "vue-i18n";

export default {
    name: "ListValueComponent",
    emits: ["onSelectObject"],
    props: ['entity', 'fields', 'item', 'theKey'],
    setup(props: any, {emit}) {

        const {t} = useI18n();

        const text_types = [
            "text",
            "email",
            "number",
            "tel",
            "date",
            "url",
            "color",
            "datetime-local",
            "month",
            "password",
            "time",
            "url",
            "week",
        ];
        const booleans = ["checkbox", "radio"];

        const isUndefined = computed(() => {
            return props.fields[props.theKey] === undefined
        })

        const containsImage = computed(() => {
            return Array.isArray(props.item[props.theKey]);
        });

        const isObject = computed(() => {
            return props.fields[props.theKey].attributes.type == 'object';
        });

        const isImage = computed(() => {
            return props.fields[props.theKey].attributes.type == 'image';
        });

        const getIndex = computed(() => {
            return props.fields[props.theKey].attributes.data.index;
        });

        const getImage = (url: string) => {
            return `background-image: url('${url}')`
        };

        const formatCell = (ta: any, v: string) => {
            try {
                let ic = "fa-solid icon_list fa-circle-";
                if (v == null) v = "";
                if (ta.attributes.type === "price") {
                    v = `$${(parseFloat(v)).toFixed(2)}`;
                }

                if (booleans.includes(ta.attributes.type)) {
                    v = v
                        ? `<div class="bool-td"><i class="${ic}check success_icon"></i></div>`
                        : `<div class="bool-td"><i class="${ic}xmark error_icon"></i></div>`;
                }
                if (ta.attributes.type === "icon")
                    v = `<i class="${v} icon_list"></i>`;
            } catch (e) {
                console.log("The error is ", e)
            }
            return t(v);
        }

        /**
         * @param id
         */
        const methodsLine = (id: string): void => {
            let link = props.fields[props.theKey].attributes.data.index;

            if (props.entity.object_type !== null && props.entity.object_type !== undefined ) {
                link = `${link}?object_type=${props.entity.object_type.id}`;
            }
            emit('onSelectObject', id, 'SHOW', link)
        };

        const formatJson = (text: any) => {
            if (text == null || text == undefined) return "";
            return typeof text === "object" ? `${text.id} : ${text.name}` : t(text);
        };

        return {
            containsImage,
            isUndefined,
            isObject,
            isImage,
            getIndex,
            getImage,
            formatCell,
            formatJson,
            methodsLine
        }
    }
}
