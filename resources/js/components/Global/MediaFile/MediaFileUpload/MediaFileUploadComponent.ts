import { computed, getCurrentInstance, nextTick, ref } from "vue";
import { v4 as uuidv4 } from "uuid";
import cts from "@/components/Global/Constants";

export default {
    emits: ["onSelected"],
    props: [
        "isImage",
        "post",
        "csrf",
        "multiple",
        "is_mobile",
        "accept",
        "field_id",
        "field_layout",
        "isObjectSelector",
    ],
    setup(props: any, { emit }) {
        /* refresh */
        const instance = getCurrentInstance();

        /* Data */
        const Cts = cts;
        const media_files = ref<any>([]);
        const media_file_active = ref<any>(null);
        const uploading = ref<boolean>(false);
        const progress = ref<number>(0);
        const percent = ref<number>(0);
        const totalSize = ref<number>(0);
        const allFields = ref<any>([]);
        const oneFiled = ref<any>({});

        const urlAjax = computed(() => {
            return props.isImage
                ? props.post
                : `${props.post}?field=${props.field_id}&layout=${props.field_layout}`;
        });

        /**
         * @param input
         */
        const show_media_file_preview = (input: any) => {
            input = input.target;
            if (input.files) {
                input = Array.from(input.files);
                input.forEach((file) => {
                    const reader = new FileReader();
                    reader.readAsDataURL(file);

                    reader.onload = props.isImage
                        ? forceOnLoadImage
                        : forceOnLoadFile;

                    file["title"] = file.name;
                    file["reader"] = reader;
                    file["uuid"] = uuidv4().toString();
                    file["error"] = null;
                });
                media_files.value = media_files.value.concat(input);
            }
            if (media_files.value.length > 0)
                media_file_active.value = media_files.value[0];


            /*
                - Select the first element of the table with classe table-remove and add the class selected-td-table
                - This code is executed only the first time when there are no elements to display in the table
             */
            if (media_files.value.length === 1) {
                nextTick(() => {
                    changeActiveMediaFile(media_files.value[0]);
                });
            }
        };

        /**
         * @param list
         */
        const uploadTheMediaFile = (list: any) => {
            uploading.value = true;
            resetLoader();
            for (let i = 0; i < list.length; i++) {
                totalSize.value += Number(list[i].size);
            }

            // for (let i = 0; i < list.length; i++) {
            //     startUploading(list[i]);
            // }

            const allPromise = list.map((item) => startUploading(item));

            Promise.all(allPromise)
                .then((item) => {
                    if (props.multiple === false) {
                        emit("onSelected", [oneFiled.value]);
                    }
                    if (props.multiple === true) {
                        emit("onSelected", allFields.value);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        };

        /**
         * @param list
         */
        const removeTheMediaFile = (item: any) => {
            media_files.value = media_files.value.filter(
                (the_file) => the_file.uuid !== item.uuid
            );
            if (
                media_file_active.value.uuid === item.uuid &&
                media_files.value.length > 0
            )
                media_file_active.value = media_files.value[0];
        };

        /**
         * @param item
         */
        const changeActiveMediaFile = (item: any) => {
            media_file_active.value = item;
            /* Select border class */
            if (!props.is_mobile) {
                const tableRemove = document.querySelector(".table-remove");
                const tableRows = tableRemove.querySelectorAll("tr");

                // "'tr' + item.uuid + '-tab'"
                const tdSelect = document.getElementById(
                    "tr" + item.uuid + "-tab"
                );
                nextTick(() => {
                    tableRows.forEach((row) =>
                        row.classList.remove("selected-td-table")
                    );
                    tdSelect.classList.add("selected-td-table");
                });
            }
        };

        /**
         * @param item
         */
        const resetLoader = () => {
            progress.value = 0;
            percent.value = 0;
            totalSize.value = 0;
        };

        /**
         *
         */
        const forceOnLoadFile = () => {
            for (let i = 0; i < media_files.value.length; i++) {
                const element = document.querySelector(
                    "#obj_" + media_files.value[i].uuid
                );
                if (element == null) return;
                element.setAttribute(
                    "data",
                    media_files.value[i].reader.result
                );
            }
        };

        /**
         *
         */
        const forceOnLoadImage = () => {
            for (let i = 0; i < media_files.value.length; i++) {
                let element = document.querySelector<HTMLImageElement>(
                    "#img_" + media_files.value[i].uuid
                );
                if (element == null) return;
                element.setAttribute("src", media_files.value[i].reader.result);
                element.onload = () => {
                    instance?.proxy?.$forceUpdate();
                    media_files.value[i].width = element.naturalWidth;
                    media_files.value[i].height = element.naturalHeight;
                };
            }
        };

        /**
         * @param item
         */
        const startUploading = (item: any) => {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                if (props.isImage) {
                    formData.append("name", item.title);
                    formData.append("height", item.height);
                    formData.append("width", item.width);
                    formData.append("image", item);
                } else {
                    formData.append("name", item.title);
                    formData.append("the_file", item);
                }

                jQuery.ajax(urlAjax.value, {
                    method: "POST",
                    data: formData,
                    headers: { "X-CSRF-TOKEN": props.csrf },
                    processData: false,
                    contentType: false,
                    xhr: () => {
                        const xhr = new XMLHttpRequest();
                        let currentLoaded = 0;
                        xhr.upload.onprogress = (e) => {
                            currentLoaded += e.loaded - currentLoaded;
                            progress.value += currentLoaded;
                            percent.value =
                                (progress.value / totalSize.value) * 100;

                            document.querySelector<HTMLElement>(
                                props.isImage
                                    ? "#image_progress_bar"
                                    : "#the_file_progress_bar"
                            ).style.width = percent.value.toFixed(2) + "%";
                        };
                        return xhr;
                    },
                    success: (_success) => {
                        if (props.multiple === false) {
                            oneFiled.value = _success;
                        }
                        if (props.multiple === true) {
                            allFields.value.push(_success);
                        }
                        removeTheMediaFile(item);
                        resolve(item);
                    },
                    error: (error) => {
                        media_files.value = media_files.value.map((item) => {
                            if (item.uuid === item.uuid)
                                item.error = error.responseText.toString();
                            return item;
                        });
                        resetLoader();

                        reject(error);
                    },
                    complete: (data) => {
                        if (
                            percent.value >= 100 &&
                            media_files.value.length === 0
                        ) {
                            resetLoader();
                            uploading.value = false;
                        }
                    },
                });
            });
        };

        return {
            /* Data */
            Cts,
            media_files,
            media_file_active,
            uploading,
            progress,
            percent,
            totalSize,
            allFields,
            oneFiled,
            /* Methods */
            show_media_file_preview,
            // uploadTheFile,
            uploadTheMediaFile,
            removeTheMediaFile,
            changeActiveMediaFile,
            resetLoader,
            // forceOnLoad,
            startUploading,
        };
    },
};
