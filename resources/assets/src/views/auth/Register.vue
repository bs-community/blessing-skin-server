<template>
  <form>
    <div class="form-group has-feedback">
      <input
        ref="email"
        v-model="email"
        type="email"
        class="form-control"
        :placeholder="$t('auth.email')"
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
    <div class="form-group has-feedback">
      <input
        ref="confirm"
        v-model="confirm"
        type="password"
        class="form-control"
        :placeholder="$t('auth.repeat-pwd')"
      >
      <span class="glyphicon glyphicon-log-in form-control-feedback" />
    </div>

    <div
      v-if="requirePlayer"
      class="form-group has-feedback"
      :title="$t('auth.player-name-intro')"
      data-placement="top"
      data-toggle="tooltip"
    >
      <input
        ref="playerName"
        v-model="playerName"
        type="text"
        class="form-control"
        :placeholder="$t('auth.player-name')"
      >
      <span class="glyphicon glyphicon-pencil form-control-feedback" />
    </div>
    <div
      v-else
      class="form-group has-feedback"
      :title="$t('auth.nickname-intro')"
      data-placement="top"
      data-toggle="tooltip"
    >
      <input
        ref="nickname"
        v-model="nickname"
        type="text"
        class="form-control"
        :placeholder="$t('auth.nickname')"
      >
      <span class="glyphicon glyphicon-pencil form-control-feedback" />
    </div>

    <div class="row">
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
      <div class="col-xs-8">
        <a v-t="'auth.login-link'" :href="`${baseUrl}/auth/login`" class="text-center" />
      </div>
      <div class="col-xs-4">
        <button v-if="pending" disabled class="btn btn-primary btn-block btn-flat">
          <i class="fa fa-spinner fa-spin" /> {{ $t('auth.registering') }}
        </button>
        <button
          v-else
          class="btn btn-primary btn-block btn-flat"
          @click.prevent="submit"
        >
          {{ $t('auth.register-button') }}
        </button>
      </div>
    </div>
  </form>
</template>

<script>
import { swal } from '../../js/notify'

export default {
  name: 'Register',
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data: () => ({
    email: '',
    password: '',
    confirm: '',
    nickname: '',
    playerName: '',
    captcha: '',
    time: Date.now(),
    infoMsg: '',
    warningMsg: '',
    pending: false,
    requirePlayer: blessing.extra.player,
  }),
  methods: {
    async submit() {
      const {
        email, password, confirm, playerName, nickname, captcha,
      } = this

      if (!email) {
        this.infoMsg = this.$t('auth.emptyEmail')
        this.$refs.email.focus()
        return
      }

      if (!/\S+@\S+\.\S+/.test(email)) {
        this.infoMsg = this.$t('auth.invalidEmail')
        this.$refs.email.focus()
        return
      }

      if (!password) {
        this.infoMsg = this.$t('auth.emptyPassword')
        this.$refs.password.focus()
        return
      }

      if (password.length < 8 || password.length > 32) {
        this.infoMsg = this.$t('auth.invalidPassword')
        this.$refs.password.focus()
        return
      }

      if (password !== confirm) {
        this.infoMsg = this.$t('auth.invalidConfirmPwd')
        this.$refs.confirm.focus()
        return
      }

      if (this.requirePlayer && !playerName) {
        this.infoMsg = this.$t('auth.emptyPlayerName')
        this.$refs.playerName.focus()
        return
      }

      if (!this.requirePlayer && !nickname) {
        this.infoMsg = this.$t('auth.emptyNickname')
        this.$refs.nickname.focus()
        return
      }

      if (!captcha) {
        this.infoMsg = this.$t('auth.emptyCaptcha')
        this.$refs.captcha.focus()
        return
      }

      this.pending = true
      const { errno, msg } = await this.$http.post(
        '/auth/register',
        Object.assign({
          email,
          password,
          captcha,
        }, this.requirePlayer ? { player_name: playerName } : { nickname })
      )
      if (errno === 0) {
        swal({ type: 'success', text: msg })
        setTimeout(() => {
          window.location = `${blessing.base_url}/user`
        }, 1000)
      } else {
        this.infoMsg = ''
        this.warningMsg = msg
        this.refreshCaptcha()
        this.pending = false
      }
    },
    refreshCaptcha() {
      this.time = Date.now()
    },
  },
}
</script>
