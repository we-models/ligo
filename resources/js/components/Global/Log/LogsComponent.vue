<template>
    <div>
        <div class="header-list">
            <div class="header-side">
                <SearchComponent :page="page" :url="url" sort="id" sort_direction="asc" @onList="onList"
                    @onProgress="onProgress" @onResetPage="onResetPage" />
            </div>
            <PaginationComponent :pagination="pagination" @onChange="changePage" />
        </div>

        <div v-if="!progress">
            <div class="table-responsive" v-if="!sizeStore.isMobile">
                <table class="table table-bordered table-logs-component">
                    <thead class="thead-dark">
                    <tr>
                        <th class="list-title">{{ $t('ID') }}</th>
                        <th class="list-title">{{ $t('Causer') }}</th>
                        <th class="list-title">{{ $t('Description') }}</th>
                        <th class="list-title">{{ $t('Date time') }}</th>
                        <th class="list-title">{{ $t('Details') }}</th>
                    </tr>
                    </thead>
                    <tbody v-for="log in pagination.data" >
                    <tr>
                        <td>{{ log.id }}</td>
                        <td>
                            <p v-if="log.causer !== null">
                                {{ log.causer.name }}
                                <a :href="'mailto:' + log.causer.email">{{ log.causer.email }}</a>
                            </p>
                        </td>
                        <td> {{$t(log.description)}}</td>
                        <td>
                            {{ this.Cts.reformatDateTime(log.created_at) }}
                        </td>
                        <td>
                            <a class="btn btn-primary" data-bs-toggle="collapse" :href="'#collapse_' + log.id"
                               role="button" aria-expanded="false" :aria-controls="'collapse_' + log.id">
                                {{ $t('Toggle details') }}
                            </a>

                        </td>
                    </tr>

                    <!-- Collapse -->
                    <tr>
                        <td :colspan="5" class="collapse" :id="'collapse_' + log.id">

                            <div class="card card-body log_objects">
                                <div v-if="log.properties.attributes !== undefined" class="log-objects-new">
                                    <h5 class="title-table">{{ $t('How it is now') }}</h5>
                                    <hr>

                                    <div v-for="key in Object.keys(log.properties.attributes)"
                                         class="row log_row_detail">
                                        <div class="col-3">
                                            <strong class="log_title">
                                                {{ $t(Cts.reformatTitle(key).toLowerCase()) }}
                                            </strong>
                                        </div>
                                        <div class="col-9" v-html="Cts.formatLog(key, log.properties.attributes[key])">
                                        </div>
                                    </div>
                                </div>

                                <hr class="separator-hr" v-if="log.properties.old !== undefined">

                                <div v-if="log.properties.old !== undefined" class="log-objects-old">

                                    <h5 class="title-table">{{ $t('As it was before') }}</h5>
                                    <hr>

                                    <div v-for="key in Object.keys(log.properties.old)" class="row log_row_detail">
                                        <div class="col-3">
                                            <strong class="log_title">
                                                {{ $t(Cts.reformatTitle(key).toLowerCase()) }}</strong>
                                        </div>
                                        <div class="col-9" v-html="Cts.formatLog(key, log.properties.old[key])">
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="items-card">
                <div class="card" v-for="log in pagination.data" >
                    <div>
                        <table class="table table-bordered table-mobile-logs">
                            <tr>
                                <th class="list-title">
                                    <strong>
                                        {{ $t('ID') }}
                                    </strong>
                                </th>
                                <td>
                                    {{ log.id }}
                                </td>
                            </tr>
                            <tr>
                                <th class="list-title">
                                    <strong>
                                        {{ $t('Causer') }}
                                    </strong>
                                </th>
                                <td>
                                    <p v-if="log.causer !== null">
                                        {{ log.causer.name }}
                                        <a :href="'mailto:' + log.causer.email">{{ log.causer.email }}</a>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th class="list-title">
                                    <strong>
                                        {{ $t('Description') }}
                                    </strong>
                                </th>
                                <td>{{ log.description }}</td>
                            </tr>

                            <tr>
                                <th class="list-title">
                                    <strong>
                                        {{ $t('Date time') }}
                                    </strong>
                                </th>
                                <td>
                                    {{ this.Cts.reformatDateTime(log.created_at) }}
                                </td>
                            </tr>

                        </table>

                        <div class="btn_icons button-link-table-mobile-logs">
                            <a class="btn btn-primary" data-bs-toggle="collapse" :href="'#collapse_' + log.id" role="button"
                               aria-expanded="false" :aria-controls="'collapse_' + log.id">
                                {{ $t('Toggle details') }}
                            </a>

                        </div>

                        <!-- Collapse -->
                        <div class="collapse" :id="'collapse_' + log.id">


                            <div class="card card-body log_objects ">
                                <div v-if="log.properties.attributes !== undefined" class="log-objects-new">
                                    <h5 class="title-table">{{ $t('How it is now') }}</h5>
                                    <hr>

                                    <div v-for="key in Object.keys(log.properties.attributes)" class="row log_row_detail">
                                        <div class="col-5 title-collapse-table">
                                            <strong class="log_title">
                                                {{ $t(Cts.reformatTitle(key).toLowerCase()) }}
                                            </strong>
                                        </div>
                                        <div class="col-7 content-table-mobile" v-html="Cts.formatLog(key, log.properties.attributes[key])">
                                        </div>
                                    </div>
                                </div>

                                <hr class="separator-hr" v-if="log.properties.old !== undefined">

                                <div v-if="log.properties.old !== undefined" class="log-objects-old">

                                    <h5 class="title-table">{{ $t('As it was before') }}</h5>
                                    <hr>

                                    <div v-for="key in Object.keys(log.properties.old)" class="row log_row_detail">
                                        <div class="col-5 title-collapse-table">
                                            <strong class="log_title">
                                                {{ $t(Cts.reformatTitle(key).toLowerCase()) }}</strong>
                                        </div>
                                        <div class="col-7 content-table-mobile" v-html=" Cts.formatLog(key, log.properties.old[key])">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>
            </div>
        </div>

        <InterfaceLoaderComponent v-if="progress" />
    </div>
</template>


<script lang="ts" src="./LogsComponent.ts" />

<style scoped src="./LogsComponent.css" />
