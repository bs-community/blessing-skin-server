import Vue from 'vue'

export default Vue.extend({
  filters: {
    truncate(text: string = ''): string {
      return text.length > 15 ? `${text.slice(0, 15)}...` : text
    },
  },
})
