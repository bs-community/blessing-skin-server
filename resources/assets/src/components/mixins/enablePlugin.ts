import Vue from 'vue'
import { showModal } from '../../scripts/notify'

export default Vue.extend({
  data: () => ({ plugins: [] }),
  methods: {
    async enablePlugin({
      name, dependencies: { all }, originalIndex,
    }: {
      name: string
      dependencies: { all: Record<string, string> }
      originalIndex: number
    }) {
      if (Object.keys(all).length === 0) {
        try {
          await showModal({
            text: this.$t('admin.noDependenciesNotice'),
            okButtonType: 'warning',
          })
        } catch {
          return
        }
      }

      const {
        code, message, data: { reason } = { reason: [] },
      } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'enable', name },
      ) as { code: number, message: string, data: { reason: string[] } }
      if (code === 0) {
        this.$message.success(message)
        this.$set(this.plugins[originalIndex], 'enabled', true)
      } else {
        const div = document.createElement('div')
        const p = document.createElement('p')
        p.textContent = message
        div.appendChild(p)
        const ul = document.createElement('ul')
        reason.forEach(item => {
          const li = document.createElement('li')
          li.textContent = item
          ul.appendChild(li)
        })
        div.appendChild(ul)
        showModal({
          mode: 'alert',
          dangerousHTML: div.outerHTML,
        })
      }
    },
  },
})
