import toastr from 'toastr'
import { swal } from '../../js/notify'

export default {
  methods: {
    async removeClosetItem() {
      const { dismiss } = await swal({
        text: this.$t('user.removeFromClosetNotice'),
        type: 'warning',
        showCancelButton: true,
      })
      if (dismiss) {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/closet/remove',
        { tid: this.tid }
      )
      if (errno === 0) {
        this.$emit('item-removed')
        swal({ type: 'success', text: msg })
      } else {
        toastr.warning(msg)
      }
    },
  },
}
