<template>
    <div class="pd-top" >
        <label for="">
            <div :class="field.row === 6? 'sub-relation' : 'sub-relation-head'"  v-if="field.has_relations">
                <a href="javascript:void(0)" v-on:click="openSubRelation" >
                    <i class="fa-solid fa-filter"></i>
                    {{field.name}} <p><small>({{field.slug}})</small></p>
                </a>
            </div>
            <div v-else>
                <strong >{{field.name}} </strong>
                <p><small> ({{field.slug}})</small> </p>
            </div>
        </label>
        <div v-if="field.row === 12">
            <object-type-fields-filter-component
                :object_type="this.field.relation"
                :filter_link="this.filter_link" ref="ObjectFields"
                :readonly = "this.progress"
                :csrf = "this.csrf"
                :prefix="this.the_prefix"
            />
        </div>
        <div v-if="field.row === 6">
            <div class="mg-bt" v-if="this.filterStore.exists(this.filter)">
                <select
                    class="form-control"
                    id=""
                    @change="onChangeFilterType($event)"
                    :readonly="readonly">
                    <option
                        v-for="cond in [{label:'IN', value : $t('IN')}, {label:'IN', value : $t('NOT IN')} ] "
                        :value="cond.label">
                        {{cond.value}}
                    </option>
                </select>
            </div>
            <object-selector-component
                :ent = "this.entity"
                :llave="field.slug"
                :value = "field.selector.attributes"
                :csrf = "csrf"
                :required = "false"
                :readonly = "progress"
                :multiple="true"
                :paginate = "12"
                type = "object"
                :depends = "[]"
                @onSetSelectedItems = "changeFilter"
            />
        </div>
    </div>
</template>

<script>
import {nextTick, onMounted, reactive} from 'vue';
import { v4 as uuidv4 } from 'uuid';
import { useFilterStore } from '../../stores/FilterStore.js';

export default {
    props : ['field', 'csrf', 'progress', 'filter_link', 'onFilterIsDone', 'prefix'],
    name: "ObjectFilterComponent",
    setup(props){
        const entity = reactive({});
        const filterStore = useFilterStore();
        const the_uuid = uuidv4().toString();


        const the_prefix = `${props.prefix}.${props.field.slug}`;


        const filter = reactive({
            uuid: the_uuid,
            layout: 'relation',
            object_type : props.field.relation.id,
            left: null,
            right: null,
            type: 'IN'
        });

        onMounted(()=>{
           entity[props.field.slug] = [];
        });

        const openSubRelation = function (){
            props.field.row = props.field.row === 6 ? 12 : 6;
            filterStore.removeAll(`.${props.field.slug}.`);
        };

        const changeFilter = function (k, s){
            k= the_prefix;
            if (s == null || s === []) {
                filterStore.remove(filter);
            } else {
                filter.left = k;
                filter.right = s.map(v=>v.id);

                if (filterStore.exists(filter)) {
                    filterStore.update(filter);
                } else {
                    filterStore.add(filter);
                }
            }
        };

        const onChangeFilterType = function(event) {
            filter.type = event.target.value;
            filterStore.update(filter);
        };

        return {
            the_prefix,
            entity,
            filterStore,
            filter,
            openSubRelation,
            onChangeFilterType,
            changeFilter
        };
    }
}
</script>

<style scoped>

</style>
