<template>
  <div>
    <vue-good-table
      :rows="lines"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'operations'">
          <el-button size="medium" @click="modify(props.row)">
            {{ $t('admin.i18n.modify') }}
          </el-button>
          <el-button type="danger" size="medium" @click="remove(props.row)">
            {{ $t('admin.i18n.delete') }}
          </el-button>
        </span>
        <span v-else-if="props.column.field === 'text'">
          <span v-if="props.row.text" v-text="props.formattedRow[props.column.field]" />
          <i v-else>{{ $t('admin.i18n.empty') }}</i>
        </span>
        <span v-else v-text="props.formattedRow[props.column.field]" />
      </template>
    </vue-good-table>
  </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import tableOptions from '../../components/mixins/tableOptions'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'Translations',
  components: {
    VueGoodTable,
  },
  mixins: [
    emitMounted,
    tableOptions,
  ],
  data() {
    return {
      lines: [],
      columns: [
        { field: 'group', label: this.$t('admin.i18n.group') },
        { field: 'key', label: this.$t('admin.i18n.key') },
        { field: 'text', label: this.$t('admin.i18n.text') },
        {
          field: 'operations',
          label: this.$t('admin.operationsTitle'),
          sortable: false,
          globalSearchDisabled: true,
        },
      ],
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.lines = await this.$http.get('/admin/i18n/list')
    },
    async modify(line) {
      let text = null
      try {
        ({ value: text } = await this.$prompt(this.$t('admin.i18n.updating'), {
          inputValue: line.text,
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.put(
        '/admin/i18n',
        { id: line.id, text }
      )
      if (code === 0) {
        line.text = text
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async remove({ id, originalIndex }) {
      try {
        await this.$confirm(this.$t('admin.i18n.confirmDelete'), {
          type: 'warning',
        })
      } catch {
        return
      }

      const { message } = await this.$http.del('/admin/i18n', { id })
      this.$delete(this.lines, originalIndex)
      this.$message.success(message)
    },
  },
}
</script>
