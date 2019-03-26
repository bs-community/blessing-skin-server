import Vue from 'vue'
import Button from 'element-ui/lib/button'
import Message from 'element-ui/lib/message'
import MessageBox from 'element-ui/lib/message-box'

Vue.use(Button)

Vue.prototype.$message = Message
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt

export {
  Message,
  MessageBox,
}
