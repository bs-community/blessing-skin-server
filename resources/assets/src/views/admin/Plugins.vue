<template>
  <section class="content">
    <vue-good-table
      :rows="plugins"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
      :row-style-class="rowStyleClassFn"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'title'">
          <strong>{{ props.formattedRow[props.column.field] }}</strong>
          <div v-if="props.row.enabled" class="actions">
            <template v-if="props.row.config">
              <a
                v-t="'admin.configurePlugin'"
                class="text-primary"
                :href="`${baseUrl}/admin/plugins/config/${props.row.name}`"
              /> |
            </template>
            <a
              v-t="'admin.disablePlugin'"
              href="#"
              class="text-primary"
              @click="disablePlugin(props.row)"
            />
          </div>
          <div v-else class="actions">
            <a
              v-t="'admin.enablePlugin'"
              href="#"
              class="text-primary"
              @click="enablePlugin(props.row)"
            /> |
            <a
              v-t="'admin.deletePlugin'"
              href="#"
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
  name: 'Plugins',
  components: {
    VueGoodTable,
  },
  mixins: [
    emitMounted,
    enablePlugin,
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
      plugins: [],
      columns: [
        {
          field: 'title', label: this.$t('admin.pluginTitle'), width: '17%',
        },
        {
          field: 'description',
          label: this.$t('admin.pluginDescription'),
          sortable: false,
          width: '65%',
        },
        {
          field: 'dependencies',
          label: this.$t('admin.pluginDependencies'),
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
      this.plugins = await this.$http.get('/admin/plugins/data')
    },
    rowStyleClassFn(row) {
      return row.enabled ? 'plugin-enabled' : 'plugin'
    },
    async disablePlugin({ name, originalIndex }) {
      const { code, message } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'disable', name }
      )
      if (code === 0) {
        this.$message.success(message)
        this.plugins[originalIndex].enabled = false
      } else {
        this.$message.warning(message)
      }
    },
    async deletePlugin({ name, originalIndex }) {
      try {
        await this.$confirm(this.$t('admin.confirmDeletion'), { type: 'warning' })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'delete', name }
      )
      if (code === 0) {
        this.$delete(this.plugins, originalIndex)
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.actions
  margin-top 5px
  color #ddd

.plugin-version-author
  color #777
  font-size small
    a
      color #337ab7

.plugin > td:first-child
  border-left 5px solid transparent

.plugin-enabled
  background-color #f7fcfe

.plugin-enabled > td:first-child
  border-left 5px solid #3c8dbc
</style>
