<template>
    <section class="content">
        <vue-good-table
            mode="remote"
            :rows="players"
            :totalRows="totalRecords || players.length"
            :columns="columns"
            :search-options="tableOptions.search"
            :pagination-options="tableOptions.pagination"
            @on-page-change="onPageChange"
            @on-sort-change="onSortChange"
            @on-search="onSearch"
            @on-per-page-change="onPerPageChange"
            styleClass="vgt-table striped"
        >
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field === 'uid'">
                    <a
                        :href="`${baseUrl}/admin/users?uid=${props.row.uid}`"
                        :title="$t('admin.inspectHisOwner')"
                        data-toggle="tooltip"
                        data-placement="right"
                    >{{ props.formattedRow[props.column.field] }}</a>
                </span>
                <span v-else-if="props.column.field === 'preview'">
                    <a
                        v-if="props.row.tid_steve"
                        :href="`${baseUrl}/skinlib/show/${props.row.tid_steve}`"
                    >
                        <img :src="`${baseUrl}/preview/64/${props.row.tid_steve}.png`" width="64">
                    </a>
                    <a
                        v-if="props.row.tid_alex"
                        :href="`${baseUrl}/skinlib/show/${props.row.tid_alex}`"
                    >
                        <img :src="`${baseUrl}/preview/64/${props.row.tid_alex}.png`" width="64">
                    </a>
                    <a
                        v-if="props.row.tid_cape"
                        :href="`${baseUrl}/skinlib/show/${props.row.tid_cape}`"
                    >
                        <img :src="`${baseUrl}/preview/64/${props.row.tid_cape}.png`" width="64">
                    </a>
                </span>
                <span v-else-if="props.column.field === 'operations'">
                    <div class="btn-group">
                        <button
                            class="btn btn-default dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >{{ $t('admin.changeTexture') }} <span class="caret"></span></button>
                        <ul class="dropdown-menu" data-test="change-texture">
                            <li><a @click="changeTexture(props.row, 'steve')" href="#steve">Steve</a></li>
                            <li><a @click="changeTexture(props.row, 'alex')" href="#alex">Alex</a></li>
                            <li><a @click="changeTexture(props.row, 'cape')" v-t="'general.cape'" href="#cape"></a></li>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <button
                            class="btn btn-default dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >{{ $t('general.more') }} <span class="caret"></span></button>
                        <ul class="dropdown-menu" data-test="operations">
                            <li><a @click="changeName(props.row)" v-t="'admin.changePlayerName'" href="#"></a></li>
                            <li><a @click="togglePreference(props.row)" v-t="'admin.changePreference'" href="#"></a></li>
                            <li><a @click="changeOwner(props.row)" v-t="'admin.changeOwner'" href="#"></a></li>
                        </ul>
                    </div>
                    <button
                        class="btn btn-danger"
                        v-t="'admin.deletePlayer'"
                        @click="deletePlayer(props.row)"
                    ></button>
                </span>
                <span v-else v-text="props.formattedRow[props.column.field]" />
            </template>
        </vue-good-table>
    </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.min.css';
import { swal } from '../../js/notify';
import toastr from 'toastr';

export default {
    name: 'PlayersManagement',
    components: {
        VueGoodTable
    },
    props: {
        baseUrl: {
            default: blessing.base_url
        }
    },
    data() {
        return {
            players: [],
            totalRecords: 0,
            columns: [
                { field: 'pid', label: 'PID', type: 'number' },
                { field: 'player_name', label: this.$t('general.player.player-name') },
                { field: 'uid', label: this.$t('general.player.owner'), type: 'number' },
                { field: 'preference', label: this.$t('general.player.preference'), globalSearchDisabled: true },
                { field: 'preview', label: this.$t('general.player.previews'), globalSearchDisabled: true, sortable: false },
                { field: 'last_modified', label: this.$t('general.player.last-modified') },
                { field: 'operations', label: this.$t('admin.operationsTitle'), globalSearchDisabled: true, sortable: false },
            ],
            serverParams: {
                sortField: 'pid',
                sortType: 'asc',
                page: 1,
                perPage: 10,
                search: '',
            },
            tableOptions: {
                search: {
                    enabled: true,
                    placeholder: this.$t('vendor.datatable.search')
                },
                pagination: {
                    enabled: true,
                    nextLabel: this.$t('vendor.datatable.next'),
                    prevLabel: this.$t('vendor.datatable.prev'),
                    rowsPerPageLabel: this.$t('vendor.datatable.rowsPerPage'),
                    allLabel: this.$t('vendor.datatable.all'),
                    ofLabel: this.$t('vendor.datatable.of')
                }
            },
        };
    },
    beforeMount() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            const { data, totalRecords } = await this.$http.get(
                `/admin/player-data${location.search}`,
                !location.search && this.serverParams
            );
            this.totalRecords = totalRecords;
            this.players = data;
        },
        onPageChange(params) {
            this.serverParams.page = params.currentPage;
            this.fetchData();
        },
        onPerPageChange(params) {
            this.serverParams.perPage = params.currentPerPage;
            this.fetchData();
        },
        onSortChange(params) {
            this.serverParams.sortType = params.sortType;
            this.serverParams.sortField = this.columns[params.columnIndex].field;
            this.fetchData();
        },
        onSearch(params) {
            this.serverParams.search = params.searchTerm;
            this.fetchData();
        },
        async changeTexture(player, model) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.pidNotice'),
                input: 'number',
                inputValue: player[`tid_${model}`]
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/players?action=texture',
                { pid: player.pid, model, tid: value }
            );
            if (errno === 0) {
                player[`tid_${model}`] = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async changeName(player) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.changePlayerNameNotice'),
                input: 'text',
                inputValue: player.player_name,
                inputValidator: name => !name && this.$t('admin.emptyPlayerName')
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/players?action=name',
                { pid: player.pid, name: value }
            );
            if (errno === 0) {
                player.player_name = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async togglePreference(player) {
            const preference = player.preference === 'default' ? 'slim' : 'default';
            const { errno, msg } = await this.$http.post(
                '/admin/players?action=preference',
                { pid: player.pid, preference }
            );
            if (errno === 0) {
                player.preference = preference;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async changeOwner(player) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.changePlayerOwner'),
                input: 'number',
                inputValue: player.uid
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/players?action=owner',
                { pid: player.pid, uid: value }
            );
            if (errno === 0) {
                player.uid = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async deletePlayer(player) {
            const { dismiss } = await swal({
                text: this.$t('admin.deletePlayerNotice'),
                type: 'warning',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/players?action=delete',
                { pid: player.pid }
            );
            if (errno === 0) {
                this.players = this.players.filter(({ pid }) => pid !== player.pid);
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        }
    }
};
</script>
