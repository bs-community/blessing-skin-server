import Vue from 'vue'
import { showModal, toast } from '../../scripts/notify'
import { truthy } from '../../scripts/validators'

export default Vue.extend<{
  name: string
  tid: number
}, { addClosetItem(): Promise<void> }, {}>({
  methods: {
    async addClosetItem() {
      let value: string
      try {
        ({ value } = await showModal({
          mode: 'prompt',
          title: this.$t('skinlib.setItemName'),
          text: this.$t('skinlib.applyNotice'),
          input: this.name,
          validator: truthy(this.$t('skinlib.emptyItemName')),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/closet/add',
        { tid: this.tid, name: value },
      )
      if (code === 0) {
        toast.success(message!)
        this.$emit('like-toggled', true)
      } else {
        toast.error(message!)
      }
    },
  },
})
