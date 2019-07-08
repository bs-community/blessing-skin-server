<template>
  <div v-if="recaptcha" class="row">
    <div class="col-xs-12" style="padding-bottom: 5px">
      <vue-recaptcha
        ref="recaptcha"
        :size="invisible ? 'invisible' : ''"
        :sitekey="recaptcha"
        @verify="onVerify"
      />
    </div>
  </div>
  <div v-else class="row">
    <div class="col-xs-8">
      <div class="form-group has-feedback">
        <input
          ref="captcha"
          v-model="value"
          type="text"
          class="form-control"
          :placeholder="$t('auth.captcha')"
          required
        >
      </div>
    </div>
    <div class="col-xs-4">
      <img
        class="pull-right captcha"
        :src="`${baseUrl}/auth/captcha?v=${time}`"
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
      default: blessing.base_url,
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
