import { defineStore } from "pinia";

export const useGlobalStore = defineStore("global", {
    state: () => {
        return {
            APP_DEBUG: null as string | null,
        };
    },
    actions: {
        updateAppDebugFromWindow() {
            if (typeof window !== "undefined") {
                const windowWithDebug = window as typeof window & {
                    APP_DEBUG: any;
                };

                this.APP_DEBUG = windowWithDebug.APP_DEBUG;
            } else {
                this.APP_DEBUG = null;
            }
        },
    },
});
