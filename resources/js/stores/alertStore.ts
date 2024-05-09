import {defineStore} from 'pinia'

export const useAlertStore = defineStore('alert', {
    state: () => {
        return {
            success: '',
            error: '',
        }
    },
})
