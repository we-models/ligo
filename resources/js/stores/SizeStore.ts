import {defineStore} from 'pinia'

export const useSizeStore = defineStore('size', {
    state: () => {
        return {
            height : 0,
            width : 0,
            contentWidth : 0
        }
    },
    getters: {
        isMobile: (state) => state.width <= 768,
        isTablet: (state) => state.width <= 1200,
    },
})
