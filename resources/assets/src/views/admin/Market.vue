<template>
  <section class="content">
    <vue-good-table
      :rows="plugins"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
    >
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'title'">
          <strong>{{ props.formattedRow[props.column.field] }}</strong>
          <div>{{ props.row.name }}</div>
        </span>
        <span v-else-if="props.column.field === 'dependencies'">
          <span
            v-if="props.row.dependencies.requirements.length === 0"
          ><i v-t="'admin.noDependencies'" /></span>
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
        <span v-else-if="props.column.field === 'operations'">
          <template v-if="props.row.installed">
            <button
              v-if="props.row.update_available"
              class="btn btn-success btn-sm"
              :disabled="installing === props.row.name"
              @click="updatePlugin(props.row)"
            >
              <template v-if="installing === props.row.name">
                <i class="fas fa-spinner fa-spin" /> {{ $t('admin.pluginUpdating') }}
              </template>
              <template v-else>
                <i class="fas fa-sync-alt" /> {{ $t('admin.updatePlugin') }}
              </template>
            </button>
            <button v-else-if="props.row.enabled" class="btn btn-primary btn-sm" disabled>
              <i class="fas fa-check" /> {{ $t('admin.statusEnabled') }}
            </button>
            <button v-else class="btn btn-primary btn-sm" @click="enablePlugin(props.row)">
              <i class="fas fa-plug" /> {{ $t('admin.enablePlugin') }}
            </button>
          </template>
          <button
            v-else
            class="btn btn-default btn-sm"
            :disabled="installing === props.row.name"
            @click="installPlugin(props.row)"
          >
            <template v-if="installing === props.row.name">
              <i class="fas fa-spinner fa-spin" /> {{ $t('admin.pluginInstalling') }}
            </template>
            <template v-else>
              <i class="fas fa-download" /> {{ $t('admin.installPlugin') }}
            </template>
          </button>
        </span>
        <span v-else v-text="props.formattedRow[props.column.field]" />
      </template>
    </vue-good-table>
  </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import tableOptions from '../../components/mixins/tableOptions'

export default {
  name: 'Market',
  components: {
    VueGoodTable,
  },
  mixins: [
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

      const { errno, msg } = await this.$http.post(
        '/admin/plugins/market/download',
        { name }
      )
      if (errno === 0) {
        this.$message.success(msg)
        this.plugins[originalIndex].update_available = false
        this.plugins[originalIndex].installed = true
      } else {
        this.$message.warning(msg)
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
    async enablePlugin({
      name, dependencies: { requirements }, originalIndex,
    }) {
      if (requirements.length === 0) {
        try {
          await this.$confirm(
            this.$t('admin.noDependenciesNotice'),
            { type: 'warning' }
          )
        } catch {
          return
        }
      }

      const {
        errno, msg, reason,
      } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'enable', name }
      )
      if (errno === 0) {
        this.$message.success(msg)
        this.$set(this.plugins[originalIndex], 'enabled', true)
      } else {
        const div = document.createElement('div')
        const p = document.createElement('p')
        p.textContent = msg
        div.appendChild(p)
        const ul = document.createElement('ul')
        reason.forEach(item => {
          const li = document.createElement('li')
          li.textContent = item
          ul.appendChild(li)
        })
        div.appendChild(ul)
        this.$alert(div.innerHTML.replace(/`([\w-_]+)`/g, '<code>$1</code>'), {
          dangerouslyUseHTMLString: true,
          type: 'warning',
        })
      }
    },
  },
}
</script>
