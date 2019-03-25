import Vue from 'vue'
import Message from 'element-ui/lib/message'
import MessageBox from 'element-ui/lib/message-box'

Vue.prototype.$message = Message
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

window.MessageBox = MessageBox

export {
  Message,
  MessageBox,
}
