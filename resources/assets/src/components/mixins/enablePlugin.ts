import Vue from 'vue'

export default Vue.extend({
  data: () => ({ plugins: [] }),
  methods: {
    async enablePlugin({
      name, dependencies: { requirements }, originalIndex,
    }: {
      name: string,
      dependencies: { requirements: string[] },
      originalIndex: number
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
        code, message, reason,
      } = await this.$http.post(
        '/admin/plugins/manage',
        { action: 'enable', name }
      ) as { code: number, message: string, reason: string[] }
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
        this.$alert(div.innerHTML.replace(/`([\w-_]+)`/g, '<code>$1</code>'), {
          dangerouslyUseHTMLString: true,
          type: 'warning',
        })
      }
    },
  },
})
