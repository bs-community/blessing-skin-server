<template>
  <div v-if="recaptcha" class="row">
    <div class="col-xs-12" style="padding-bottom: 5px">
      <vue-recaptcha
        ref="recaptcha"
        :sitekey="recaptcha"
        @verify="$emit('change', $event)"
      />
    </div>
  </div>
  <div v-else class="row">
    <div class="col-xs-8">
      <div class="form-group has-feedback">
        <input
          ref="captcha"
          type="text"
          class="form-control"
          :placeholder="$t('auth.captcha')"
          @input="$emit('change', $event.target.value)"
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
        @click="refreshCaptcha"
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
      time: Date.now(),
      recaptcha: blessing.extra.recaptcha,
    }
  },
  methods: {
    refreshCaptcha() {
      if (this.recaptcha) {
        this.$refs.recaptcha.reset()
      } else {
        this.time = Date.now()
      }
    },
  },
}
</script>
