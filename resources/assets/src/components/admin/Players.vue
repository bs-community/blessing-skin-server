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
                <span v-if="props.column.field === 'player_name'">
                    {{ props.formattedRow[props.column.field] }}
                    <a @click="changeName(props.row)" :title="$t('admin.changePlayerName')" data-test="name">
                        <i class="fas fa-edit btn-edit"></i>
                    </a>
                </span>
                <span v-else-if="props.column.field === 'uid'">
                    <a
                        :href="`${baseUrl}/admin/users?uid=${props.row.uid}`"
                        :title="$t('admin.inspectHisOwner')"
                        data-toggle="tooltip"
                        data-placement="right"
                    >{{ props.formattedRow[props.column.field] }}</a>
                    <a @click="changeOwner(props.row)" :title="$t('admin.changeOwner')" data-test="owner">
                        <i class="fas fa-edit btn-edit"></i>
                    </a>
                </span>
                <span v-else-if="props.column.field === 'preview'">
                    <a
                        v-if="props.row.tid_skin"
                        :href="`${baseUrl}/skinlib/show/${props.row.tid_skin}`"
                    >
                        <img :src="`${baseUrl}/preview/64/${props.row.tid_skin}.png`" width="64">
                    </a>
                    <a
                        v-if="props.row.tid_cape"
                        :href="`${baseUrl}/skinlib/show/${props.row.tid_cape}`"
                    >
                        <img :src="`${baseUrl}/preview/64/${props.row.tid_cape}.png`" width="64">
                    </a>
                </span>
                <span v-else-if="props.column.field === 'operations'">
                    <button
                        class="btn btn-default"
                        data-toggle="modal"
                        data-target="#modal-change-texture"
                        v-t="'admin.changeTexture'"
                        @click="textureChanges.originalIndex = props.row.originalIndex"
                    ></button>
                    <button
                        class="btn btn-danger"
                        v-t="'admin.deletePlayer'"
                        @click="deletePlayer(props.row)"
                    ></button>
                </span>
                <span v-else v-text="props.formattedRow[props.column.field]" />
            </template>
        </vue-good-table>

        <div
            id="modal-change-texture"
            class="modal fade"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        ><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" v-t="'admin.changeTexture'"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label v-t="'admin.textureType'" />
                            <select class="form-control" v-model="textureChanges.type">
                                <option value="skin" v-t="'general.skin'"></option>
                                <option value="cape" v-t="'general.cape'"></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>TID</label>
                            <input
                                class="form-control"
                                type="text"
                                :placeholder="$t('admin.pidNotice')"
                                v-model.number="textureChanges.tid"
                            >
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                            v-t="'general.close'"
                        ></button>
                        <a @click="changeTexture" class="btn btn-primary" v-t="'general.submit'"></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
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
            textureChanges: {
                originalIndex: -1,
                type: 'skin',
                tid: '',
            }
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
        async changeTexture() {
            const player = this.players[this.textureChanges.originalIndex];
            const { type, tid } = this.textureChanges;

            const { errno, msg } = await this.$http.post(
                '/admin/players?action=texture',
                { pid: player.pid, type, tid }
            );
            if (errno === 0) {
                player[`tid_${type}`] = tid;
                toastr.success(msg);
                $('.modal').modal('hide');
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
        async deletePlayer({ pid, originalIndex }) {
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
                { pid }
            );
            if (errno === 0) {
                this.$delete(this.players, originalIndex);
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        }
    }
};
</script>
