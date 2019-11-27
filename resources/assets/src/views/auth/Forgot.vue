<template>
  <form @submit.prevent="submit">
    <div class="input-group mb-3">
      <input
        v-model="email"
        type="email"
        class="form-control"
        :placeholder="$t('auth.email')"
        required
      >
      <div class="input-group-append">
        <div class="input-group-text">
          <span class="fas fa-envelope" />
        </div>
      </div>
    </div>

    <captcha ref="captcha" />

    <div class="alert alert-success" :class="{ 'd-none': !successMsg }">
      <i class="icon fas fa-check" />
      {{ successMsg }}
    </div>
    <div class="alert alert-warning" :class="{ 'd-none': !warningMsg }">
      <i class="icon fas fa-exclamation-triangle" />
      {{ warningMsg }}
    </div>

    <div class="d-flex justify-content-between">
      <a v-t="'auth.forgot.login-link'" :href="`${baseUrl}/auth/login`" class="text-center" />
      <button
        class="btn btn-primary"
        type="submit"
        :disabled="pending"
      >
        <template v-if="pending">
          <i class="fa fa-spinner fa-spin" /> {{ $t('auth.sending') }}
        </template>
        <span v-else>{{ $t('auth.forgot.button') }}</span>
      </button>
    </div>
  </form>
</template>

<script>
import Captcha from '../../components/Captcha.vue'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'Forgot',
  components: {
    Captcha,
  },
  mixins: [
    emitMounted,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data: () => ({
    email: '',
    successMsg: '',
    warningMsg: '',
    pending: false,
  }),
  methods: {
    async submit() {
      const { email } = this

      this.pending = true
      const { code, message } = await this.$http.post(
        '/auth/forgot',
        { email, captcha: await this.$refs.captcha.execute() },
      )
      if (code === 0) {
        this.warningMsg = ''
        this.successMsg = message
        this.pending = false
      } else {
        this.warningMsg = message
        this.pending = false
        this.$refs.captcha.refresh()
      }
    },
  },
}
</script>
