import {useI18n} from "vue-i18n";

export default {
    name: "PaginationComponent",
    props: ['pagination'],
    emits: ["onChange"],
    setup(props: any, {emit}) {
        const {t} = useI18n();
        const changePage = (pg: string) => {
            let url = new URL(pg);
            const urlParams = new URLSearchParams(url.search);
            const page = parseInt(urlParams.get('page'));
            emit('onChange', page);
        }

        const getPage = (pg: any) => {
            if (!isNaN(pg)) return pg;
            return t(pg);
        }

        return {
            changePage,
            getPage
        }
    }
}
