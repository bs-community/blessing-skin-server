import Vue from 'vue'

export default Vue.extend({
  data() {
    return {
      tableOptions: {
        search: {
          enabled: true,
          placeholder: this.$t('vendor.datatable.search'),
        },
        pagination: {
          enabled: true,
          nextLabel: this.$t('vendor.datatable.next'),
          prevLabel: this.$t('vendor.datatable.prev'),
          rowsPerPageLabel: this.$t('vendor.datatable.rowsPerPage'),
          allLabel: this.$t('vendor.datatable.all'),
          ofLabel: this.$t('vendor.datatable.of'),
        },
      },
    }
  },
})
