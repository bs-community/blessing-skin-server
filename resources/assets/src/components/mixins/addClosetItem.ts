import Vue from 'vue'
import { MessageBoxInputData } from 'element-ui/types/message-box'

export default Vue.extend<{
  name: string,
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
          }
        ) as MessageBoxInputData)
      } catch {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/closet/add',
        { tid: this.tid, name: value }
      )
      if (errno === 0) {
        this.$message.success(msg!)
        this.$emit('like-toggled', true)
      } else {
        this.$message.warning(msg!)
      }
    },
  },
})
