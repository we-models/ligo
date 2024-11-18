import {computed, onMounted} from "vue";

export default {
    props: ['fields', 'theKey'],
    name: "SortComponent",
    emits: ["onChangeSort"],
    setup(props: any, {emit}) {

        const isSortable = computed(()=>{
            return props.fields[props.theKey].properties.sortable;
        });

        const isUndefined = computed(() => {
            return props.fields[props.theKey] == undefined;
        });

        const directionUp = computed(() => {
            return (props.fields[props.theKey].direction !== 'asc' ? '' : 'bt-blue') + ' fa-solid fa-caret-up'
        });

        const directionDown = computed(() => {
            return (props.fields[props.theKey].direction !== 'desc' ? '' : 'bt-blue') + ' fa-solid fa-caret-down';
        });

        const refillDirection = (value: string) => {
            emit('onChangeSort', props.theKey, value);
        }

        return {
            isUndefined,
            directionUp,
            directionDown,
            refillDirection,
            isSortable
        }
    }
}
