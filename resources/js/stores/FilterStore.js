import { defineStore } from 'pinia'

export const useFilterStore = defineStore('filter', {
    state: () => ({ filters: [] }),
    actions: {
        add(filter) {
            if(['LIKE', 'NOT LIKE'].includes(filter.type)){
                filter.right = `%${filter.right}%`
            }
            this.filters.push(filter);
        },
        clean(){
            this.filters = [];
        },
        remove(filter){
            this.filters = this.filters.filter(f => f.uuid  !== filter.uuid);
        },
        removeAll(code){
            this.filters = this.filters.filter((f)=> !f.left.includes(code));
        },
        update(filter){
            if(filter.right.length === 0){
                this.remove(filter);
            }else{
                this.filters = this.filters.map((f)=>{
                    if(f.uuid === filter.uuid) {
                        f.type = filter.type;
                        f.left = filter.left;
                        f.right = filter.right;

                        if(['LIKE', 'NOT LIKE'].includes(filter.type)){
                            filter.right = `%${filter.right}%`
                        }
                    }
                    return f;
                });
            }
        },
        exists(filter){
            return (this.filters.filter(f => f.uuid  === filter.uuid)).length > 0;
        }
    },
})
