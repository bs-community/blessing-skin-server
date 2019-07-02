import Vue from 'vue'
import * as emitter from '../../scripts/event'

export default Vue.extend({
  mounted() {
    emitter.emit('mounted', { el: this.$root.$options.el })
  },
})
