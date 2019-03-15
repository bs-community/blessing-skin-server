<template>
  <form>
    <div class="form-group has-feedback">
      <input
        ref="identification"
        v-model="identification"
        type="email"
        class="form-control"
        :placeholder="$t('auth.identification')"
      >
      <span class="glyphicon glyphicon-envelope form-control-feedback" />
    </div>
    <div class="form-group has-feedback">
      <input
        ref="password"
        v-model="password"
        type="password"
        class="form-control"
        :placeholder="$t('auth.password')"
      >
      <span class="glyphicon glyphicon-lock form-control-feedback" />
    </div>

    <div v-if="tooManyFails" class="row">
      <div class="col-xs-8">
        <div class="form-group has-feedback">
          <input
            ref="captcha"
            v-model="captcha"
            type="text"
            class="form-control"
            :placeholder="$t('auth.captcha')"
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

    <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
    <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

    <div class="row">
      <div class="col-xs-6">
        <div class="checkbox icheck" style="margin-top: 0;">
          <label>
            <input v-model="remember" type="checkbox"> {{ $t('auth.keep') }}
          </label>
        </div>
      </div>
      <div class="col-xs-6">
        <a v-t="'auth.forgot-link'" class="pull-right" :href="`${baseUrl}/auth/forgot`" />
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <button v-if="pending" disabled class="btn btn-primary btn-block btn-flat">
          <i class="fa fa-spinner fa-spin" /> {{ $t('auth.loggingIn') }}
        </button>
        <button
          v-else
          class="btn btn-primary btn-block btn-flat"
          @click.prevent="login"
        >
          {{ $t('auth.login') }}
        </button>
      </div>
    </div>
  </form>
</template>

<script>
import { swal } from '../../js/notify'

export default {
  name: 'Login',
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      identification: '',
      password: '',
      captcha: '',
      remember: false,
      time: Date.now(),
      tooManyFails: blessing.extra.tooManyFails,
      infoMsg: '',
      warningMsg: '',
      pending: false,
    }
  },
  methods: {
    async login() {
      const {
        identification, password, captcha, remember,
      } = this

      if (!identification) {
        this.infoMsg = this.$t('auth.emptyIdentification')
        this.$refs.identification.focus()
        return
      }

      if (!password) {
        this.infoMsg = this.$t('auth.emptyPassword')
        this.$refs.password.focus()
        return
      }

      if (this.tooManyFails && !captcha) {
        this.infoMsg = this.$t('auth.emptyCaptcha')
        this.$refs.captcha.focus()
        return
      }

      this.pending = true
      const {
        errno, msg, login_fails: loginFails,
      } = await this.$http.post(
        '/auth/login',
        {
          identification,
          password,
          keep: remember,
          captcha: this.tooManyFails ? captcha : undefined,
        }
      )
      if (errno === 0) {
        swal({ type: 'success', text: msg })
        setTimeout(() => {
          window.location = `${blessing.base_url}/${blessing.redirect_to || 'user'}`
        }, 1000)
      } else {
        if (loginFails > 3 && !this.tooManyFails) {
          swal({ type: 'error', text: this.$t('auth.tooManyFails') })
          this.tooManyFails = true
        }
        this.refreshCaptcha()
        this.infoMsg = ''
        this.warningMsg = msg
        this.pending = false
      }
    },
    refreshCaptcha() {
      this.time = Date.now()
    },
  },
}
</script>

<style lang="stylus">
#login-button {
    margin-top: 5px;
}
</style>
