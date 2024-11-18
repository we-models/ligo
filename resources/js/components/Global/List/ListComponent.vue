<template>
    <div>
        <alert-component/>

        <div class="header-list">


            <div class="header-side">

                <div class="list_selected" v-if="selected != null && selected.length > 0">
                    <ul class="list-group">
                        <li v-for="item in selected"
                            class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fl-bold"> {{ item.id }}: {{ item.name }}
                                </div>
                            </div>
                            <a href="javascript:void(0)" v-on:click="removeSelected(item)">
                                <span class="text-danger"><i class="fa-solid fa-xmark"></i></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div :class="searchComponentClass()">
                    <SearchComponent
                    :page="page"
                    :sort="sort"
                    :sort_direction="sort_direction"
                    :url="url"
                    @onList="onList"
                    @onProgress="onProgress"
                    @onResetPage="onResetPage"
                    />
                </div>


            </div>

            <PaginationComponent :pagination="pagination" @onChange="changePage"/>

        </div>

        <div class="table-responsive" v-if="!sizeStore.isMobile">
            <table class="table table-bordered" v-if="!progress">
                <thead class="thead-dark">
                <tr>
                    <th v-if="this.multiple == null" class="btn_icons list-title">
                        {{ $t('Options') }}
                    </th>
                    <th v-if="this.multiple != null" class="list-title">
                        {{ $t('Select') }}
                    </th>
                    <th v-for="(field, key) in entity">
                        <SortComponent :fields="all_fields" :theKey="key" @onChangeSort="refillDirection"/>
                    </th>
                </tr>
                </thead>
                <tbody v-for="item in pagination.data" :class="selectedItemClassDesk(item.id)">
                <tr
                    :class="name_choose != null ? 'possible-selection' : ''"
                    v-on:click="setSelectionByRow('inp_' + item['id'])"
                    v-on:dblclick="selectAndSend('inp_' + item['id'])">
                    <td v-if="this.multiple == null" class="btn_icons">
                        <a v-if="permissions.includes('.show')" class="btn_view"
                           href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'SHOW', this.index)">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a v-if="permissions.includes('.edit')" class="btn_edit"
                           href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'PUT', this.index)">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a v-if="permissions.includes('.destroy')" class="btn_delete" href="javascript:void(0)"
                           v-on:click="deleteItem(item)">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                    <td v-if="this.multiple && name_choose != null">
                        <input :id="'inp_' + item['id']" :checked="checkedVerify(item)" :class="'checkbox_'+name_choose"
                               :data-name-item="item['name']"
                               :name="name_choose" :value="item['id']"
                               readonly type="checkbox"
                               class="input-checkbox-list"
                               v-on:change="setSelection($event, item)">
                    </td>
                    <td v-if="!this.multiple && name_choose != null">
                        <input class="input-check-list" :id="'inp_' + item['id']" :name="name_choose" :value="item['id']" readonly
                               type="radio" v-on:click="setSelection($event, item)" >
                    </td>
                    <td v-for="(field, key) in entity">
                        <ListValueComponent
                            :entity="entity"
                            :fields="all_fields"
                            :item="item"
                            :the-key="key"
                            @onSelectObject="methodsLine"
                        />
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="items-card">
            <div :class="selectedItemClassMobile(item.id)"  v-for="item in pagination.data" v-if="!progress">
                <div
                    :class="name_choose != null ? 'possible-selection' : ''"
                    v-on:click="setSelectionByRow('inp_' + item['id'])"
                    v-on:dblclick="selectAndSend('inp_' + item['id'])"

                >

                    <div v-if="this.multiple && name_choose != null">
                        <input :id="'inp_' + item['id']"
                           :checked="checkedVerify(item)" :class="'checkbox_'+name_choose"
                           :data-name-item="item['name']"
                           :name="name_choose" :value="item['id']"
                           readonly type="checkbox"
                           class="input-checkbox-list"
                           v-on:change="setSelection($event, item)">
                    </div>

                    <div v-if="!this.multiple && name_choose != null" >
                        <input
                            :id="'inp_' + item['id']"
                            :name="name_choose"
                            :value="item['id']"
                            readonly
                            class="input-radio-list"
                            type="radio"
                            v-on:click="setSelection($event, item)">
                    </div>
                    <table class="table table-bordered">
                        <tr v-for="(field, key) in entityResume">
                            <th class="list-title">
                                <strong class="strong-title" v-if="all_fields[key] != undefined">
                                    {{$t(all_fields[key].properties.label)}}
                                </strong>
                                <strong class="strong-title" v-else>
                                    {{ $t(`${key}`)}}
                                </strong>
                            </th>
                            <td class="td-class-mobile">
                                <ListValueComponent
                                    :entity="entity"
                                    :fields="all_fields"
                                    :item="item"
                                    :the-key="key"
                                    @onSelectObject="methodsLine"
                                />
                            </td>
                        </tr>
                    </table>

                    <div class="btn_icons">
                        <a v-if="permissions.includes('.show')" class="btn_view"
                           href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'SHOW', this.index)">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a v-if="permissions.includes('.edit')" class="btn_edit"
                           href="javascript:void(0)" v-on:click="methodsLine(item['id'], 'PUT', this.index)">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a v-if="permissions.includes('.destroy')" class="btn_delete" href="javascript:void(0)"
                           v-on:click="deleteItem(item)">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>


        <InterfaceLoaderComponent v-if="progress"/>

        <nav v-if="this.multiple != null" class="nav justify-content-start button-mark-selected">
            <ul class="pagination">
                <button id="markAsSelectedButton" :disabled="this.required && this.selected.length === 0"
                        class="btn btn-primary" type="button"
                        @click="$emit('onSelected', selected)">
                    {{ $t('Mark as selected') }}
                </button>
            </ul>
        </nav>
        <modal-form-component v-if="this.multiple == null" v-once/>
    </div>
</template>


<script lang="ts" src="./ListComponent.ts"/>

<style scoped src="./ListComponent.css"/>
