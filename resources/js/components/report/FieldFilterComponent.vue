<template>
    <div class="pd-top" v-if="this.filterStore!== null">
        <strong>
            {{field.name}}
        </strong>
        <p>
            <small>({{field.slug}})</small>
        </p>
        <div class="flex-field">
            <div style="flex: 1" v-if="this.filterStore.exists(this.filter)">
                <select
                    class="form-control"
                    id=""
                    @change="onChangeFilterType($event)"
                    v-model="this.filter.type"
                    :readonly="readonly">
                    <option
                        v-for="cond in this.getFilterOptions(field.type.id) "
                        :value="cond">
                        {{cond}}
                    </option>
                </select>
            </div>
            <div style="flex: 2">
                <field-component
                    v-if="this.entity[field.slug] != null"
                    :ent="this.entity[field.slug]"
                    :name = "this.field.slug"
                    :type = "this.field.type"
                    :field = "this.field"
                    :readonly = "this.readonly"
                    @onSetSelectedItems = changeInputFilter
                />
            </div>
        </div>
    </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid';
import { ref, onMounted , reactive} from 'vue'
import { useFilterStore } from '../../stores/FilterStore.js';

export default {
    props: ['field', 'readonly', 'onFilterIsDone', 'prefix'],
    name: "FieldFilterComponent",
    setup(props) {

        const entity = reactive({});

        const filterStore = useFilterStore();

        const the_uuid = uuidv4().toString();

        const the_prefix = `${props.prefix}.${props.field.slug}`;

        const filter = reactive({
            uuid: the_uuid,
            layout: 'field',
            left: null,
            right: null,
            type: '='
        });

        onMounted(() => {
            entity[props.field.slug] = '';
        });

        const changeInputFilter = function(k, s) {
            k= the_prefix;
            if (s == null) {
                filterStore.remove(filter);
            } else {
                filter.left = k;
                filter.right = s;

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

        const getFilterOptions = function(tp) {
            if ([1, 2, 3, 4, 5, 6].includes(tp)) return ['=', 'LIKE', 'NOT LIKE', '<>'];
            if ([7, 8, 9, 10, 11, 12].includes(tp)) return ['=', '>', '<', '<=', '>='];
            return ['=', '<>'];
        };

        return {
            entity,
            filterStore,
            filter,
            changeInputFilter,
            onChangeFilterType,
            getFilterOptions
        };
    }
};
</script>

