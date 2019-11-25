import Vue from 'vue'

export default Vue.extend({
  data: () => ({
    columns: [{ field: 'id' }],
    totalRecords: 0,
    serverParams: {
      sortField: 'id',
      sortType: 'asc',
      page: 1,
      perPage: 10,
      search: '',
    },
  }),
  created() {
    this.serverParams.sortField = this.columns[0].field
  },
  methods: {
    fetchData(): Promise<void> {
      return Promise.resolve()
    },
    onPageChange(params: { currentPage: number }) {
      this.serverParams.page = params.currentPage
      this.fetchData()
    },
    onPerPageChange(params: { currentPerPage: number }) {
      this.serverParams.perPage = params.currentPerPage
      this.fetchData()
    },
    onSortChange([params]: Array<{ type: 'asc' | 'desc', field: string }>) {
      this.serverParams.sortType = params.type
      this.serverParams.sortField = params.field
      this.fetchData()
    },
    onSearch(params: { searchTerm: string }) {
      this.serverParams.search = params.searchTerm
      this.fetchData()
    },
  },
})
