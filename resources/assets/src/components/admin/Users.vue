<template>
    <section class="content">
        <vue-good-table
            mode="remote"
            :rows="users"
            :totalRows="totalRecords || users.length"
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
                <span v-if="props.column.field === 'players_count'">
                    <a
                        :href="props.row | playersLink"
                        :title="$t('admin.inspectHisPlayers')"
                        data-toggle="tooltip"
                        data-placement="right"
                    >{{ props.formattedRow[props.column.field] }}</a>
                </span>
                <span v-else-if="props.column.field === 'permission'">
                    {{ props.row | humanizePermission }}
                </span>
                <span v-else-if="props.column.field === 'verified'">
                    <span v-if="props.row.verified" v-t="'admin.verified'"></span>
                    <span v-else v-t="'admin.unverified'"></span>
                </span>
                <div v-else-if="props.column.field === 'operations'">
                    <div class="btn-group">
                        <button
                            class="btn btn-default dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >{{ $t('general.more') }} <span class="caret"></span></button>
                        <ul class="dropdown-menu operations-menu">
                            <li><a @click="changeEmail(props.row)" v-t="'admin.changeEmail'" href="#"></a></li>
                            <li><a @click="toggleVerification(props.row)" v-t="'admin.toggleVerification'" href="#"></a></li>
                            <li><a @click="changeNickName(props.row)" v-t="'admin.changeNickName'" href="#"></a></li>
                            <li><a @click="changePassword(props.row)" v-t="'admin.changePassword'" href="#"></a></li>
                            <li><a @click="changeScore(props.row)" v-t="'admin.changeScore'" href="#"></a></li>
                            <template v-if="props.row.permission < 2">
                                <li class="divider"></li>
                                <li v-if="props.row.operations >= 2 && props.row.permission > -1">
                                    <a
                                        @click="toggleAdmin(props.row)"
                                        v-t="props.row.permission === 0 ? 'admin.setAdmin' : 'admin.unsetAdmin'"
                                        href="#"
                                    ></a>
                                </li>
                                <li v-if="props.row.permission < 1">
                                    <a
                                        @click="toggleBan(props.row)"
                                        v-t="props.row.permission === 0 ? 'admin.ban' : 'admin.unban'"
                                        href="#"
                                    ></a>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <button
                        :disabled="props.row.permission >= 2 || (props.row.operations === 1 && props.row.permission >= 1)"
                        class="btn btn-danger"
                        v-t="'admin.deleteUser'"
                        @click="deleteUser(props.row)"
                    ></button>
                </div>
                <span v-else v-text="props.formattedRow[props.column.field]" />
            </template>
        </vue-good-table>
    </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.min.css';
import { trans } from '../../js/i18n';
import { swal } from '../../js/notify';
import toastr from 'toastr';

export default {
    name: 'UsersManagement',
    components: {
        VueGoodTable
    },
    filters: {
        humanizePermission(user) {
            switch (user.permission) {
                case -1:
                    return trans('admin.banned');
                case 0:
                    return trans('admin.normal');
                case 1:
                    return trans('admin.admin');
                case 2:
                    return trans('admin.superAdmin');
            }
        },
        playersLink(user) {
            return `${blessing.base_url}/admin/players?uid=${user.uid}`;
        }
    },
    data() {
        return {
            users: [],
            totalRecords: 0,
            columns: [
                { field: 'uid', label: 'UID', type: 'number' },
                { field: 'email', label: this.$t('general.user.email') },
                { field: 'nickname', label: this.$t('general.user.nickname'), width: '150px' },
                { field: 'score', label: this.$t('general.user.score'), type: 'number', width: '102px' },
                { field: 'players_count', label: this.$t('admin.playersCount'), type: 'number' },
                { field: 'permission', label: this.$t('admin.status'), globalSearchDisabled: true },
                { field: 'verified', label: this.$t('admin.verification'), type: 'boolean', globalSearchDisabled: true },
                { field: 'register_at', label: this.$t('general.user.register-at') },
                { field: 'operations', label: this.$t('admin.operationsTitle'), sortable: false, globalSearchDisabled: true }
            ],
            serverParams: {
                sortField: 'uid',
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
                `/admin/user-data${location.search}`,
                !location.search && this.serverParams
            );
            this.totalRecords = totalRecords;
            this.users = data;
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
        async changeEmail(user) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.newUserEmail'),
                showCancelButton: true,
                input: 'email',
                inputValue: user.email,
                inputValidator: value => !value && this.$t('auth.emptyEmail')
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/users?action=email',
                { uid: user.uid, email: value }
            );
            if (errno === 0) {
                user.email = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async toggleVerification(user) {
            const { errno, msg } = await this.$http.post(
                '/admin/users?action=verification',
                { uid: user.uid }
            );
            if (errno === 0) {
                user.verified = !user.verified;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async changeNickName(user) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.newUserNickname'),
                showCancelButton: true,
                input: 'text',
                inputValue: user.nickname,
                inputValidator: value => !value && this.$t('auth.emptyNickname')
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/users?action=nickname',
                { uid: user.uid, nickname: value }
            );
            if (errno === 0) {
                user.nickname = value;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async changePassword(user) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.newUserPassword'),
                showCancelButton: true,
                input: 'password',
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/users?action=password',
                { uid: user.uid, password: value }
            );
            errno === 0 ? toastr.success(msg) : toastr.warning(msg);
        },
        async changeScore(user) {
            const { dismiss, value } = await swal({
                text: this.$t('admin.newScore'),
                showCancelButton: true,
                input: 'number',
                inputValue: user.score
            });
            if (dismiss) {
                return;
            }
            const score = Number.parseInt(value);

            const { errno, msg } = await this.$http.post(
                '/admin/users?action=score',
                { uid: user.uid, score }
            );
            if (errno === 0) {
                user.score = score;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async toggleAdmin(user) {
            const { errno, msg } = await this.$http.post(
                '/admin/users?action=admin',
                { uid: user.uid }
            );
            if (errno === 0) {
                user.permission = ~user.permission + 2;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async toggleBan(user) {
            const { errno, msg } = await this.$http.post(
                '/admin/users?action=ban',
                { uid: user.uid }
            );
            if (errno === 0) {
                user.permission = ~user.permission;
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        },
        async deleteUser({ uid, originalIndex }) {
            const { dismiss } = await swal({
                text: this.$t('admin.deleteUserNotice'),
                type: 'warning',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/users?action=delete',
                { uid }
            );
            if (errno === 0) {
                this.$delete(this.users, originalIndex);
                toastr.success(msg);
            } else {
                toastr.warning(msg);
            }
        }
    },
};
</script>

<style lang="stylus">
.operations-menu {
    margin-left -35px
}
</style>
