<template>
    <section class="content">
        <vue-good-table
            :rows="plugins"
            :columns="columns"
            :search-options="tableOptions.search"
            :pagination-options="tableOptions.pagination"
            styleClass="vgt-table striped"
            :rowStyleClass="rowStyleClassFn"
        >
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field === 'title'">
                    <strong>{{ props.formattedRow[props.column.field] }}</strong>
                    <div v-if="props.row.enabled" class="actions">
                        <template v-if="props.row.config">
                            <a
                                class="text-primary"
                                :href="`${baseUrl}/admin/plugins/config/${props.row.name}`"
                            >{{ $t('admin.configurePlugin') }}</a> |
                        </template>
                        <a
                            href="#"
                            v-t="'admin.disablePlugin'"
                            class="text-primary"
                            @click="disablePlugin(props.row)"
                        />
                    </div>
                    <div v-else class="actions">
                        <a
                            href="#"
                            v-t="'admin.enablePlugin'"
                            class="text-primary"
                            @click="enablePlugin(props.row)"
                        /> |
                        <a
                            href="#"
                            v-t="'admin.deletePlugin'"
                            class="text-danger"
                            @click="deletePlugin(props.row)"
                        />
                    </div>
                </span>
                <span v-else-if="props.column.field === 'description'">
                    <div><p>{{ props.formattedRow.description }}</p></div>
                    <div class="plugin-version-author">
                        {{ $t('admin.pluginVersion') }}
                        <span class="text-primary">{{ props.row.version }}</span> |
                        {{ $t('admin.pluginAuthor') }}
                        <a :href="props.row.url">{{ props.row.author }}</a> |
                        {{ $t('admin.pluginName') }}
                        {{ props.row.name }}
                    </div>
                </span>
                <span v-else-if="props.column.field === 'dependencies'">
                    <span
                        v-if="props.row.dependencies.requirements.length === 0"
                    ><i v-t="'admin.noDependencies'"></i></span>
                    <div v-else>
                        <span
                            v-for="(semver, dep) in props.row.dependencies.requirements"
                            :key="dep"
                            class="label"
                            :class="`bg-${dep in props.row.dependencies.unsatisfiedRequirements ? 'red' : 'green'}`"
                        >
                            {{ dep }}: {{ semver }}
                            <br>
                        </span>
                    </div>
                </span>
                <span v-else v-text="props.formattedRow[props.column.field]" />
            </template>
        </vue-good-table>
    </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.min.css';
import toastr from 'toastr';
import { swal } from '../../js/notify';

export default {
    name: 'Plugins',
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
            plugins: [],
            columns: [
                { field: 'title', label: this.$t('admin.pluginTitle'), width: '17%' },
                {
                    field: 'description',
                    label: this.$t('admin.pluginDescription'),
                    sortable: false,
                    width: '65%'
                },
                {
                    field: 'dependencies',
                    label: this.$t('admin.pluginDependencies'),
                    sortable: false,
                    globalSearchDisabled: true
                },
            ],
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
            this.plugins = await this.$http.get('/admin/plugins/data');
        },
        rowStyleClassFn(row) {
            return row.enabled ? 'plugin-enabled' : 'plugin';
        },
        async enablePlugin({ name, dependencies: { requirements }, originalIndex }) {
            if (requirements.length === 0) {
                const { dismiss } = await swal({
                    text: this.$t('admin.noDependenciesNotice'),
                    type: 'warning',
                    showCancelButton: true
                });
                if (dismiss) {
                    return;
                }
            }

            const { errno, msg, reason } = await this.$http.post(
                '/admin/plugins/manage',
                { action: 'enable', name }
            );
            if (errno === 0) {
                toastr.success(msg);
                this.$set(this.plugins[originalIndex], 'enabled', true);
            } else {
                const div = document.createElement('div');
                const p = document.createElement('p');
                p.textContent = msg;
                div.appendChild(p);
                const ul = document.createElement('ul');
                reason.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    ul.appendChild(li);
                });
                div.appendChild(ul);
                swal({
                    type: 'warning',
                    html: div.innerHTML.replace(/`([\w-_]+)`/g, '<code>$1</code>')
                });
            }
        },
        async disablePlugin({ name, originalIndex }) {
            const { errno, msg } = await this.$http.post(
                '/admin/plugins/manage',
                { action: 'disable', name }
            );
            if (errno === 0) {
                toastr.success(msg);
                this.plugins[originalIndex].enabled = false;
            } else {
                swal({ type: 'warning', text: msg });
            }
        },
        async deletePlugin({ name, originalIndex }) {
            const { dismiss } = await swal({
                text: this.$t('admin.confirmDeletion'),
                type: 'warning',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/admin/plugins/manage',
                { action: 'delete', name }
            );
            if (errno === 0) {
                this.$delete(this.plugins, originalIndex);
                toastr.success(msg);
            } else {
                swal({ type: 'warning', text: msg });
            }
        }
    }
};
</script>

<style lang="stylus">
.actions {
    margin-top: 5px;
    color: #ddd;
}

.plugin-version-author {
    color: #777;
    font-size: small;
    a {
        color: #337ab7;
    }
}

.plugin > td:first-child {
    border-left: 5px solid transparent;
}

.plugin-enabled {
    background-color: #f7fcfe;
}

.plugin-enabled > td:first-child {
    border-left: 5px solid #3c8dbc;
}
</style>
