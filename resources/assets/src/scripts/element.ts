import Vue from 'vue'
import {
  Button,
  Input,
  Message,
  MessageBox,
} from 'element-ui'

Vue.use(Button)
Vue.use(Input)

Object.assign(Vue.prototype, {
  $message: Message,
  $msgbox: MessageBox,
  $alert: MessageBox.alert,
  $confirm: MessageBox.confirm,
  $prompt: MessageBox.prompt,
})

blessing.ui = {
  message: Message,
  msgbox: MessageBox,
  alert: MessageBox.alert,
  confirm: MessageBox.confirm,
  prompt: MessageBox.prompt,
}