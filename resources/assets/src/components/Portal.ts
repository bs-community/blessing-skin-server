import Vue from 'vue'

export default Vue.extend({
  name: 'Portal',
  props: {
    selector: {
      required: true,
      type: String,
    },
    tag: {
      type: String,
      default: 'div',
    },
  },
  mounted() {
    const container = document.querySelector(this.selector)
    if (container) {
      if (container.firstChild) {
        container.replaceChild(this.$el, container.firstChild)
      } else {
        container.appendChild(this.$el)
      }
    }
  },
  render(h) {
    return h(this.tag, [this.$slots.default])
  },
})
