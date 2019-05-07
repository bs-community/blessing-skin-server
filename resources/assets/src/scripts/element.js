import Vue from 'vue'
import Button from 'element-ui/lib/button'
import Input from 'element-ui/lib/input'
import Message from 'element-ui/lib/message'
import MessageBox from 'element-ui/lib/message-box'
import Switch from 'element-ui/lib/switch'

Vue.use(Button)
Vue.use(Input)
Vue.use(Switch)

Vue.prototype.$message = Message
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

blessing.ui = {
  message: Message,
  msgbox: MessageBox,
  alert: MessageBox.alert,
  confirm: MessageBox.confirm,
  prompt: MessageBox.prompt,
}

export {
  Message,
  MessageBox,
}
