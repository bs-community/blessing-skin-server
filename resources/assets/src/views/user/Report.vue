<template>
  <section class="content">
    <vue-good-table
      :rows="reports"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'tid'">
          {{ props.formattedRow[props.column.field] }}
          <a :href="`${baseUrl}/skinlib/show/${props.row.tid}`">
            <i class="fa fa-share" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'status'">
          {{ $t(`report.status.${props.row.status}`) }}
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

export default {
  name: 'MyReports',
  components: {
    VueGoodTable,
  },
  mixins: [
    tableOptions,
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
          field: 'id', label: 'ID', type: 'number',
        },
        {
          field: 'tid', label: this.$t('report.tid'), type: 'number',
        },
        {
          field: 'reason',
          label: this.$t('report.reason'),
          sortable: false,
        },
        { field: 'status', label: this.$t('report.status-title') },
        {
          field: 'report_at',
          label: this.$t('report.time'),
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
      this.reports = await this.$http.get('/user/report-list')
    },
  },
}
</script>
