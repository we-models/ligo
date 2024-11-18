import {onMounted, ref, watch} from "vue";

export default {
    name: "SearchComponent",
    emits: ["onList", "onProgress", "onResetPage"],
    props: ['url', 'sort', 'sort_direction', 'page'],
    setup(props: any, {emit}) {
        const search = ref<string>('');
        const pagination = ref<any>({});
        const progress = ref<number>(0);
        const url_parameter = ref<string>("");
        const timer = ref<any>(null);

        watch(
            [() => props.url, () => props.sort, () => props.sort_direction, () => props.page],
            async (
                [newUrl, newSort, newSortDirection, newPage],
                [oldUrl, oldSort, oldSortDirection, oldPage]
            ) => {
                fillList('').then(r => {
                });
            }
        );

        onMounted(() => {
            getParams(props.url);
            fillList('');
        });


        const onKeyUp = async () => {
            emit("onResetPage", 1);
            if(timer.value != null){
                clearTimeout(timer.value);
            }
            timer.value = setTimeout(async () => {
                fillList('');
            }, 500);
        }

        const onChange = () => {
            emit("onList", pagination.value);
        }

        const getParams = (uri: string) => {
            let url = new URL(uri);
            const urlParams = new URLSearchParams(url.search);
            let parameters: string = "";
            for (let paramName of urlParams.keys()) {
                let prefix = url_parameter.value.includes("?") ? "&" : "";
                parameters += `${prefix}${paramName}=${urlParams.get(paramName)}`;
            }
            url_parameter.value = parameters;
        };

        const encodeURL = (uri: string) => {

            let isNotFirst: boolean = false;
            if (uri === "") {
                uri = props.url;
                isNotFirst = true;
            }
            if (!isNotFirst && url_parameter.value.length > 0) {
                let prefix = props.url.includes("?") ? "&" : "?";
                uri += `${prefix}${url_parameter.value}`;
            }

            if (search.value !== "") {
                uri = fillUrlParameters(uri, "search", search.value);
            }

            uri = fillUrlParameters(uri, 'page', props.page);


            if (props.sort.length > 0 && props.sort_direction.length > 0) {
                uri = fillUrlParameters(uri, "sort", props.sort);
                uri = fillUrlParameters(uri, "direction", props.sort_direction);
            }

            return uri;
        };

        const fillList = async (uri: any) => {
            try {
                emit("onProgress", true);
                pagination.value = [];
                progress.value = 50;
                let the_uri = encodeURL(uri);
                const response = await fetch(the_uri);
                pagination.value = await response.json();
            } catch (error: any) {
                console.error('Error fetching data:', error);
            } finally {
                emit("onProgress", false);
                onChange();
            }
        }

        const fillUrlParameters = (uri: string, key: string, value: string) => {
            let prefix = uri.includes('?') ? '&' : '?';
            return `${uri}${prefix}${key}=${encodeURI(value)}`;
        }

        return {
            search,
            fillList,
            onKeyUp
        }
    }
}
