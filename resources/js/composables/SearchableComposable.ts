import {ref} from "vue";

export default function SearchableComposable() {

    const progress = ref<boolean>(false);

    const sort = ref<string>("id");
    const sort_direction = ref<string>("asc");

    const pagination = ref<any>({});
    const page = ref<number>(1);

    const onList = (pag: any) => {
        pagination.value = pag;
    }

    const onProgress = (prg: boolean) => {
        progress.value = prg;
    }

    const onResetPage = (pg: number) => {
        page.value = pg;
    }

    const changePage = (pg: number) => {
        page.value = pg;
    }


    return {
        progress,
        sort,
        sort_direction,
        pagination,
        page,
        onList,
        onProgress,
        onResetPage,
        changePage
    }
}
