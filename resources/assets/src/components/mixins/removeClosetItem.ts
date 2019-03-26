import Vue from 'vue'

export default Vue.extend<{
  name: string,
  tid: number
}, { removeClosetItem(): Promise<void> }, {}>({
  methods: {
    async removeClosetItem() {
      try {
        await this.$confirm(
          this.$t('user.removeFromClosetNotice'),
          { type: 'warning' }
        )
      } catch {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/closet/remove',
        { tid: this.tid }
      )
      if (errno === 0) {
        this.$emit('item-removed')
        this.$message.success(msg!)
      } else {
        this.$message.warning(msg!)
      }
    },
  },
})
