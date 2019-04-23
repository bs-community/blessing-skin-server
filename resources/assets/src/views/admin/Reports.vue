<template>
  <section class="content">
    <vue-good-table
      mode="remote"
      :rows="reports"
      :total-rows="totalRecords || reports.length"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
      @on-page-change="onPageChange"
      @on-sort-change="onSortChange"
      @on-search="onSearch"
      @on-per-page-change="onPerPageChange"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'tid'">
          {{ props.formattedRow[props.column.field] }}
          <a :href="`${baseUrl}/skinlib/show/${props.row.tid}`">{{ $t('report.check') }}</a>
          <a href="#" @click="deleteTexture(props.row)">
            {{ $t('report.delete') }}
          </a>
        </span>
        <span v-else-if="props.column.field === 'uploader'">
          {{ props.row.uploaderName }} (UID: {{ props.row.uploader }})
          <a href="#" @click="ban(props.row)">
            {{ $t('report.ban') }}
          </a>
        </span>
        <span v-else-if="props.column.field === 'reporter'">
          {{ props.row.reporterName }} (UID: {{ props.row.reporter }})
        </span>
        <span v-else-if="props.column.field === 'status'">
          {{ $t(`report.status.${props.row.status}`) }}
        </span>
        <span v-else-if="props.column.field === 'ops'">
          <el-button size="medium" @click="reject(props.row)">
            {{ $t('report.reject') }}
          </el-button>
        </span>
        <span v-else>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>
  </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import tableOptions from '../../components/mixins/tableOptions'
import serverTable from '../../components/mixins/serverTable'

export default {
  name: 'ReportsManagement',
  components: {
    VueGoodTable,
  },
  mixins: [
    tableOptions,
    serverTable,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      reports: [],
      columns: [
        {
          field: 'id', type: 'number', hidden: true,
        },
        {
          field: 'tid', label: this.$t('report.tid'), type: 'number',
        },
        { field: 'uploader', label: this.$t('skinlib.show.uploader') },
        { field: 'reporter', label: this.$t('report.reporter') },
        {
          field: 'reason',
          label: this.$t('report.reason'),
          sortable: false,
          width: '23%',
        },
        { field: 'status', label: this.$t('report.status-title') },
        {
          field: 'report_at',
          label: this.$t('report.time'),
          globalSearchDisabled: true,
        },
        {
          field: 'ops',
          label: this.$t('admin.operationsTitle'),
          globalSearchDisabled: true,
        },
      ],
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      const { data, totalRecords } = await this.$http.get(
        '/admin/report-data',
        this.serverParams
      )
      this.totalRecords = totalRecords
      this.reports = data
    },
    deleteTexture(report) {
      this.resolve(report, 'delete')
    },
    ban(report) {
      this.resolve(report, 'ban')
    },
    reject(report) {
      this.resolve(report, 'reject')
    },
    async resolve(report, action) {
      const {
        code, message, status,
      } = await this.$http.post(
        '/admin/reports',
        { id: report.id, action }
      )
      if (code === 0) {
        this.$message.success(message)
        report.status = status
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>
