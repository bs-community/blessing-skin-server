import Vue from 'vue'

export default Vue.extend<{
  name: string
  tid: number
}, { removeClosetItem(): Promise<void> }, {}>({
  methods: {
    async removeClosetItem() {
      try {
        await this.$confirm(
          this.$t('user.removeFromClosetNotice'),
          { type: 'warning' },
        )
      } catch {
        return
      }

      const { code, message } = await this.$http.post(`/user/closet/remove/${this.tid}`)
      if (code === 0) {
        this.$emit('item-removed')
        this.$message.success(message!)
      } else {
        this.$message.warning(message!)
      }
    },
  },
})
