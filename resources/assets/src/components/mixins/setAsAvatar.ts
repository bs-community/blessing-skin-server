import Vue from 'vue'
import { showModal, toast } from '../../scripts/notify'

export default Vue.extend<{
  tid: number
}, { setAsAvatar(): Promise<void> }, {}>({
  methods: {
    async setAsAvatar() {
      try {
        await showModal({
          title: this.$t('user.setAvatar'),
          text: this.$t('user.setAvatarNotice'),
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/profile/avatar',
        { tid: this.tid },
      )
      if (code === 0) {
        toast.success(message)

        Array
          .from(
            document.querySelectorAll<HTMLImageElement>('[alt="User Image"]'),
          )
          .forEach(el => (el.src += `?${new Date().getTime()}`))
      } else {
        toast.error(message)
      }
    },
  },
})
