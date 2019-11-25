import Vue from 'vue'
import { MessageBoxInputData } from 'element-ui/types/message-box'

export default Vue.extend<{
  name: string
  tid: number
}, { addClosetItem(): Promise<void> }, {}>({
  methods: {
    async addClosetItem() {
      let value: string
      try {
        ({ value } = await this.$prompt(
          this.$t('skinlib.applyNotice'),
          {
            title: this.$t('skinlib.setItemName'),
            inputValue: this.name,
            showCancelButton: true,
            inputValidator: val => !!val || this.$t('skinlib.emptyItemName'),
          },
        ) as MessageBoxInputData)
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/closet/add',
        { tid: this.tid, name: value },
      )
      if (code === 0) {
        this.$message.success(message!)
        this.$emit('like-toggled', true)
      } else {
        this.$message.warning(message!)
      }
    },
  },
})
