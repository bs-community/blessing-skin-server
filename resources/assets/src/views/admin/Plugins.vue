<template>
  <div class="container-fluid d-flex flex-wrap">
    <div v-for="(plugin, index) in plugins" :key="plugin.name" class="info-box mr-3">
      <span class="info-box-icon" :class="`bg-${plugin.icon.bg}`">
        <i :class="`${plugin.icon.faType} fa-${plugin.icon.fa}`" />
      </span>
      <div class="info-box-content">
        <div class="d-flex justify-content-between">
          <div>
            <input :checked="plugin.enabled" type="checkbox" @click.prevent="switchPlugin(plugin, $event)">&nbsp;
            <strong>{{ plugin.title }}</strong>&nbsp;
            <span class="text-gray">v{{ plugin.version }}</span>
          </div>
          <div class="plugin-actions">
            <a
              v-if="plugin.readme"
              :href="`${baseUrl}/admin/plugins/readme/${plugin.name}`"
            >
              <i class="fas fa-question" />
            </a>
            <a
              v-if="plugin.enabled && plugin.config"
              :href="`${baseUrl}/admin/plugins/config/${plugin.name}`"
            >
              <i class="fas fa-cog" />
            </a>
            <a href="#" @click="deletePlugin(plugin, index)">
              <i class="fas fa-trash" />
            </a>
          </div>
        </div>
        <div class="mt-2 plugin-desc" :title="plugin.description">
          {{ plugin.description }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import alertUnresolvedPlugins from '../../components/mixins/alertUnresolvedPlugins'
import emitMounted from '../../components/mixins/emitMounted'
import { showModal, toast } from '../../scripts/notify'

export default {
  name: 'Plugins',
  mixins: [
    emitMounted,
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
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.plugins = await this.$http.get('/admin/plugins/data')
    },
    async switchPlugin(plugin, { target }) {
      if (target.checked) {
        if (await this.enablePlugin(plugin.name)) {
          plugin.enabled = true
        }
      } else if (await this.disablePlugin(plugin.name)) {
        plugin.enabled = false
      }
    },
    async enablePlugin(name) {
      const {
        code, message, data: { reason } = { reason: [] },
      } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'enable', name },
      )
      if (code === 0) {
        toast.success(message)
      } else {
        alertUnresolvedPlugins(message, reason)
      }

      return code === 0
    },
    async disablePlugin(name) {
      const { code, message } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'disable', name },
      )
      if (code === 0) {
        toast.success(message)
      } else {
        toast.error(message)
      }

      return code === 0
    },
    async deletePlugin(plugin, index) {
      try {
        await showModal({
          title: plugin.title,
          text: this.$t('admin.confirmDeletion'),
          okButtonType: 'danger',
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'delete', name: plugin.name },
      )
      if (code === 0) {
        this.$delete(this.plugins, index)
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.info-box
  cursor default
  transition-property box-shadow
  transition-duration 0.3s
  width 32%
  @media (max-width: 1280px)
    width 47%
  @media (max-width: 768px)
    width 100%
  &:hover
    box-shadow 0 .5rem 1rem rgba(0,0,0,.15)

.info-box-content
  max-width 85%

.plugin-actions
  margin-top -7px
  a
    transition-property color
    transition-duration 0.3s
    color #000
    &:hover
      color #999
    &:not(:last-child)
      margin-right 7px

.plugin-desc
  font-size 14px
  white-space nowrap
  overflow hidden
  text-overflow ellipsis
</style>
