import Vue from 'vue'

export default Vue.extend({
  data: () => ({ plugins: [] }),
  methods: {
    async enablePlugin({
      name, dependencies: { all }, originalIndex,
    }: {
      name: string,
      dependencies: { all: { [name: string]: string } },
      originalIndex: number
    }) {
      if (Object.keys(all).length === 0) {
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
        code, message, data: { reason } = { reason: [] },
      } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'enable', name }
      ) as { code: number, message: string, data: { reason: string[] } }
      if (code === 0) {
        this.$message.success(message)
        this.$set(this.plugins[originalIndex], 'enabled', true)
      } else {
        const h = this.$createElement
        const vnode = h('div', {}, [
          h('p', message),
          h('ul', {}, reason.map(item => h('li', item))),
        ])
        this.$alert('', { message: vnode, type: 'warning' })
      }
    },
  },
})
