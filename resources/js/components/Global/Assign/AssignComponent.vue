<template>
    <alert-component/>
    <div class="assign-content">

        <div class="cards-section  " v-if="link_list !== ''">
            <div class="title-section-card">{{ formatClassName(columns) }}</div>

            <div class="search-div">
                <SearchComponent ref="searchComponent" :page="page" :url="link_list" sort="id" sort_direction="asc"
                                 @onList="onList" @onProgress="onProgress" @onResetPage="onResetPage" class="search-component" />
            </div>
            <!-- Card -->

            <div v-show="!progress || showCards" class="content-cards">
                <div v-for="entity in pagination.data" :key="entity.id">

                    <div class="card-list-assign" :class="{ 'border-card': entity.id === idSelectedEntity }"
                        @click="selectedEntity(entity)" :data-bs-toggle="sizeStore.isMobile ? 'collapse' : ''"
                        :data-bs-target="`#card-${entity.id}`" aria-expanded="false"
                        :aria-controls="`card-${entity.id}`">
                        <div class="avatar-assign">
                            <i :class="iconAssign"></i>

                        </div>

                        <div class="name-identifier">
                            <div class="ellipsis" v-if="entity.identifier !== undefined">
                                <strong>{{ entity.identifier }}</strong>
                            </div>
                            <div class="ellipsis">
                                <strong v-if="entity.identifier == undefined">{{ entity.name }}</strong>
                                <template v-else>-{{ entity.name }}</template> <br>
                            </div>
                        </div>
                    </div>
                    <!-- this assign-roles-section is used for mobile view -->
                    <div :id="`card-${entity.id}`" class="assign-roles-section collapse" v-if="sizeStore.isMobile">
                        <div class="current-roles-title">
                            {{ formatClassName(rows) }}
                        </div>

                        <div class="current-content" v-if="Object.keys(dataPermissions).length > 0">
                            <div class="current-roles-sectio" v-for="relation in dataPermissions.relations"
                                :key="relation.id">
                                <div class="current-roles-card" v-if="relation.relation">
                                    <div class="text-current-roles-card">
                                        {{ relation.name }}
                                    </div>
                                    <div class="icon-current-roles-card"
                                        @click="saveRelation(dataPermissions, relation)">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="title-add">
                            <i class="fa-solid fa-plus"></i>
                            <div class="add-roles-title">Añadir</div>
                        </div>

                        <div class="add-roles-sectio" v-if="Object.keys(dataPermissions).length > 0">
                            <div v-for="relation in dataPermissions.relations" :key="relation.id">
                                <div class="add-roles-card" v-if="relation.relation === false"
                                    @click="saveRelation(dataPermissions, relation)">
                                    {{ relation.name }}
                                </div>
                            </div>
                        </div>
                        <div v-if="sizeStore.isMobile">
                            <InterfaceLoaderComponent v-if="progress" />
                        </div>
                    </div>
                </div>

            </div>
            <PaginationComponent :pagination="pagination" @onChange="changePage" class="pagination-component" />
        </div>


        <!-- this assign-roles-section is used for desktop view -->
        <div class="assign-roles-section" v-if="!sizeStore.isMobile">
            <div class="current-roles-title">{{ formatClassName(rows) }}</div>

            <div class="current-content" v-if="Object.keys(dataPermissions).length > 0">
                <div class="current-roles-sectio" v-for="relation in dataPermissions.relations" :key="relation.id">
                    <div class="current-roles-card" v-if="relation.relation">
                        <div class="text-current-roles-card">
                            {{ relation.name }}
                        </div>
                        <div class="icon-current-roles-card" @click="saveRelation(dataPermissions, relation)">
                            <i class="fa-regular fa-trash-can"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="title-add">
                <i class="fa-solid fa-plus"></i>
                <div class="add-roles-title">Añadir</div>
            </div>

            <div class="add-roles-sectio" v-if="Object.keys(dataPermissions).length > 0">
                <div v-for="relation in dataPermissions.relations" :key="relation.id">
                    <div class="add-roles-card" v-if="relation.relation === false"
                        @click="saveRelation(dataPermissions, relation)">
                        {{ relation.name }}
                    </div>
                </div>
            </div>

            <div class="loaderComponent">

                <InterfaceLoaderComponent v-if="progress" />
            </div>
        </div>
    </div>
</template>

<script lang="ts" src="./AssignComponent.ts" />

<style scoped src="./AssignComponent.css" />
