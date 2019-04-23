<template>
  <form @submit.prevent="login">
    <div class="form-group has-feedback">
      <input
        ref="identification"
        v-model="identification"
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

    <captcha v-if="tooManyFails" ref="captcha" />

    <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
    <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

    <div class="row">
      <div class="col-xs-6">
        <el-switch v-model="remember" :active-text="$t('auth.keep')" />
      </div>
      <div class="col-xs-6">
        <a v-t="'auth.forgot-link'" class="pull-right" :href="`${baseUrl}/auth/forgot`" />
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <el-button
          type="primary"
          native-type="submit"
          :disabled="pending"
          class="auth-btn"
        >
          <template v-if="pending">
            <i class="fa fa-spinner fa-spin" /> {{ $t('auth.loggingIn') }}
          </template>
          <span v-else>{{ $t('auth.login') }}</span>
        </el-button>
      </div>
    </div>
  </form>
</template>

<script>
import Captcha from '../../components/Captcha.vue'

export default {
  name: 'Login',
  components: {
    Captcha,
  },
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
      remember: false,
      tooManyFails: blessing.extra.tooManyFails,
      recaptcha: blessing.extra.recaptcha,
      invisible: blessing.extra.invisible,
      infoMsg: '',
      warningMsg: '',
      pending: false,
    }
  },
  methods: {
    async login() {
      const {
        identification, password, remember,
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

      this.pending = true
      const {
        code, message, data: { login_fails: loginFails } = { login_fails: 0 },
      } = await this.$http.post(
        '/auth/login',
        {
          identification,
          password,
          keep: remember,
          captcha: this.tooManyFails
            ? await this.$refs.captcha.execute()
            : void 0,
        }
      )
      if (code === 0) {
        this.$message.success(message)
        setTimeout(() => {
          window.location = `${blessing.base_url}/${blessing.redirect_to || 'user'}`
        }, 1000)
      } else {
        if (loginFails > 3 && !this.tooManyFails) {
          if (this.recaptcha) {
            if (!this.invisible) {
              this.$alert(this.$t('auth.tooManyFails.recaptcha'), { type: 'error' })
            }
          } else {
            this.$alert(this.$t('auth.tooManyFails.captcha'), { type: 'error' })
          }
          this.tooManyFails = true
        }
        this.infoMsg = ''
        this.warningMsg = message
        this.pending = false
        this.$refs.captcha.refresh()
      }
    },
  },
}
</script>

<style lang="stylus">
#login-button
  margin-top 5px

.el-button
  margin-top 10px
</style>
