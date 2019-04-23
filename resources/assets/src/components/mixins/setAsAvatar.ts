import Vue from 'vue'

export default Vue.extend<{
  tid: number
}, { setAsAvatar(): Promise<void> }, {}>({
  methods: {
    async setAsAvatar() {
      try {
        await this.$confirm(
          this.$t('user.setAvatarNotice'),
          this.$t('user.setAvatar')
        )
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/profile/avatar',
        { tid: this.tid }
      )
      if (code === 0) {
        this.$message.success(message!)

        Array.from(document.querySelectorAll<HTMLImageElement>('[alt="User Image"]'))
          .forEach(el => (el.src += `?${new Date().getTime()}`))
      } else {
        this.$message.warning(message!)
      }
    },
  },
})
