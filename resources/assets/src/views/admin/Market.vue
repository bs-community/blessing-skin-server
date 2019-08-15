<template>
  <section class="content">
    <vue-good-table
      :rows="plugins"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'title'">
          <strong>{{ props.formattedRow[props.column.field] }}</strong>
          <div>{{ props.row.name }}</div>
        </span>
        <span v-else-if="props.column.field === 'dependencies'">
          <span v-if="Object.keys(props.row.dependencies.all).length === 0">
            <i v-t="'admin.noDependencies'" />
          </span>
          <div v-else>
            <span
              v-for="(constraint, name) in props.row.dependencies.all"
              :key="name"
              class="label"
              :class="`bg-${name in props.row.dependencies.unsatisfied ? 'red' : 'green'}`"
            >
              {{ name }}: {{ constraint }}
              <br>
            </span>
          </div>
        </span>
        <span v-else-if="props.column.field === 'operations'">
          <template v-if="props.row.installed">
            <el-button
              v-if="props.row.update_available"
              type="success"
              size="medium"
              :disabled="installing === props.row.name"
              @click="updatePlugin(props.row)"
            >
              <template v-if="installing === props.row.name">
                <i class="fas fa-spinner fa-spin" /> {{ $t('admin.pluginUpdating') }}
              </template>
              <template v-else>
                <i class="fas fa-sync-alt" /> {{ $t('admin.updatePlugin') }}
              </template>
            </el-button>
            <el-button
              v-else-if="props.row.enabled"
              type="primary"
              size="medium"
              disabled
            >
              <i class="fas fa-check" /> {{ $t('admin.statusEnabled') }}
            </el-button>
            <el-button
              v-else
              type="primary"
              size="medium"
              @click="enablePlugin(props.row)"
            >
              <i class="fas fa-plug" /> {{ $t('admin.enablePlugin') }}
            </el-button>
          </template>
          <el-button
            v-else
            size="medium"
            :disabled="installing === props.row.name"
            @click="installPlugin(props.row)"
          >
            <template v-if="installing === props.row.name">
              <i class="fas fa-spinner fa-spin" /> {{ $t('admin.pluginInstalling') }}
            </template>
            <template v-else>
              <i class="fas fa-download" /> {{ $t('admin.installPlugin') }}
            </template>
          </el-button>
        </span>
        <span v-else v-text="props.formattedRow[props.column.field]" />
      </template>
    </vue-good-table>
  </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import enablePlugin from '../../components/mixins/enablePlugin'
import tableOptions from '../../components/mixins/tableOptions'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'Market',
  components: {
    VueGoodTable,
  },
  mixins: [
    emitMounted,
    enablePlugin,
    tableOptions,
  ],
  data() {
    return {
      plugins: [],
      columns: [
        { field: 'title', label: this.$t('admin.pluginTitle') },
        {
          field: 'description',
          label: this.$t('admin.pluginDescription'),
          sortable: false,
          width: '37%',
        },
        { field: 'author', label: this.$t('admin.pluginAuthor') },
        {
          field: 'version',
          label: this.$t('admin.pluginVersion'),
          sortable: false,
          globalSearchDisabled: true,
        },
        {
          field: 'dependencies',
          label: this.$t('admin.pluginDependencies'),
          sortable: false,
          globalSearchDisabled: true,
        },
        {
          field: 'operations',
          label: this.$t('admin.operationsTitle'),
          sortable: false,
          globalSearchDisabled: true,
        },
      ],
      installing: '',
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.plugins = await this.$http.get('/admin/plugins/market-data')
    },
    async installPlugin({ name, originalIndex }) {
      this.installing = name

      const { code, message } = await this.$http.post(
        '/admin/plugins/market/download',
        { name }
      )
      if (code === 0) {
        this.$message.success(message)
        this.plugins[originalIndex].update_available = false
        this.plugins[originalIndex].installed = true
      } else {
        this.$message.warning(message)
      }

      this.installing = ''
    },
    async updatePlugin(plugin) {
      try {
        await this.$confirm(
          this.$t('admin.confirmUpdate', {
            plugin: plugin.title, old: plugin.installed, new: plugin.version,
          })
        )
      } catch {
        return
      }

      this.installPlugin(plugin)
    },
  },
}
</script>
