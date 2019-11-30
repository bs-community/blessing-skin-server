import Vue from 'vue'
import { Message } from 'element-ui'

Object.assign(Vue.prototype, {
  $message: Message,
})

blessing.ui = {
  message: Message,
}
