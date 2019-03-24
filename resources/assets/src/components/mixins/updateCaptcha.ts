import Vue from 'vue'

export default Vue.extend({
  data: () => ({
    captcha: '',
  }),
  methods: {
    updateCaptcha(value: string) {
      this.captcha = value
    },
  },
})
