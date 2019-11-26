<template>
  <div v-if="recaptcha" class="row">
    <div class="d-block ml-2 pb-3">
      <vue-recaptcha
        ref="recaptcha"
        :size="invisible ? 'invisible' : ''"
        :sitekey="recaptcha"
        @verify="onVerify"
      />
    </div>
  </div>
  <div v-else class="d-flex">
    <div class="form-group mb-3 mr-2">
      <input
        ref="captcha"
        v-model="value"
        type="text"
        class="form-control"
        :placeholder="$t('auth.captcha')"
        required
      >
    </div>
    <div>
      <img
        class="captcha"
        :src="`${baseUrl}auth/captcha?v=${time}`"
        alt="CAPTCHA"
        :title="$t('auth.change-captcha')"
        data-placement="top"
        data-toggle="tooltip"
        @click="refresh"
      >
    </div>
  </div>
</template>

<script>
import VueRecaptcha from 'vue-recaptcha'

export default {
  name: 'Captcha',
  components: {
    VueRecaptcha,
  },
  props: {
    baseUrl: {
      type: String,
      default: document.baseURI,
    },
  },
  data() {
    return {
      value: '',
      time: Date.now(),
      recaptcha: blessing.extra.recaptcha,
      invisible: blessing.extra.invisible,
    }
  },
  methods: {
    execute() {
      return new Promise(resolve => {
        if (this.recaptcha && this.invisible) {
          this.$refs.recaptcha.$once('verify', resolve)
          this.$refs.recaptcha.execute()
        } else {
          resolve(this.value)
        }
      })
    },
    onVerify(response) {
      this.value = response
    },
    refresh() {
      if (this.recaptcha) {
        this.$refs.recaptcha.reset()
      } else {
        this.time = Date.now()
      }
    },
  },
}
</script>
