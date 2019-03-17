import toastr from 'toastr'
import { swal } from '../../js/notify'

export default {
  methods: {
    async setAsAvatar() {
      const { dismiss } = await swal({
        title: this.$t('user.setAvatar'),
        text: this.$t('user.setAvatarNotice'),
        type: 'question',
        showCancelButton: true,
      })
      if (dismiss) {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/profile/avatar',
        { tid: this.tid }
      )
      if (errno === 0) {
        toastr.success(msg)

        Array.from(document.querySelectorAll('[alt="User Image"]'))
          .forEach(el => (el.src += `?${new Date().getTime()}`))
      } else {
        toastr.warning(msg)
      }
    },
  },
}
