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
    <div class="input-group mb-3">
      <input
        v-model="password"
        type="password"
        class="form-control"
        :placeholder="$t('auth.password')"
        required
        minlength="8"
        maxlength="32"
      >
      <div class="input-group-append">
        <div class="input-group-text">
          <span class="fas fa-lock" />
        </div>
      </div>
    </div>
    <div class="input-group mb-3">
      <input
        ref="confirm"
        v-model="confirm"
        type="password"
        class="form-control"
        :placeholder="$t('auth.repeat-pwd')"
        required
        minlength="8"
        maxlength="32"
      >
      <div class="input-group-append">
        <div class="input-group-text">
          <span class="fas fa-sign-in-alt" />
        </div>
      </div>
    </div>

    <div
      v-if="requirePlayer"
      class="input-group mb-3"
      :title="$t('auth.player-name-intro')"
      data-placement="top"
      data-toggle="tooltip"
    >
      <input
        v-model="playerName"
        type="text"
        class="form-control"
        :placeholder="$t('auth.player-name')"
        required
      >
      <div class="input-group-append">
        <div class="input-group-text">
          <span class="fas fa-gamepad" />
        </div>
      </div>
    </div>
    <div
      v-else
      class="input-group mb-3"
      :title="$t('auth.nickname-intro')"
      data-placement="top"
      data-toggle="tooltip"
    >
      <input
        v-model="nickname"
        type="text"
        class="form-control"
        :placeholder="$t('auth.nickname')"
        required
      >
      <div class="input-group-append">
        <div class="input-group-text">
          <span class="fas fa-gamepad" />
        </div>
      </div>
    </div>

    <captcha ref="captcha" />

    <div class="alert alert-info" :class="{ 'd-none': !infoMsg }">
      <i class="icon fas fa-info" />
      {{ infoMsg }}
    </div>
    <div class="alert alert-warning" :class="{ 'd-none': !warningMsg }">
      <i class="icon fas fa-exclamation-triangle" />
      {{ warningMsg }}
    </div>

    <div class="d-flex justify-content-between mb-3">
      <a v-t="'auth.login-link'" :href="`${baseUrl}/auth/login`" class="text-center" />
      <div>
        <button
          class="btn btn-primary"
          type="submit"
          :disabled="pending"
        >
          <template v-if="pending">
            <i class="fa fa-spinner fa-spin" /> {{ $t('auth.registering') }}
          </template>
          <span v-else>{{ $t('auth.register-button') }}</span>
        </button>
      </div>
    </div>
  </form>
</template>

<script>
import Captcha from '../../components/Captcha.vue'
import emitMounted from '../../components/mixins/emitMounted'
import { toast } from '../../scripts/notify'

export default {
  name: 'Register',
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
    password: '',
    confirm: '',
    nickname: '',
    playerName: '',
    infoMsg: '',
    warningMsg: '',
    pending: false,
    requirePlayer: blessing.extra.player,
  }),
  methods: {
    async submit() {
      const {
        email, password, confirm, playerName, nickname,
      } = this

      if (password !== confirm) {
        this.infoMsg = this.$t('auth.invalidConfirmPwd')
        this.$refs.confirm.focus()
        return
      }

      this.pending = true
      const { code, message } = await this.$http.post(
        '/auth/register',
        Object.assign({
          email,
          password,
          captcha: await this.$refs.captcha.execute(),
        }, this.requirePlayer ? { player_name: playerName } : { nickname }),
      )
      if (code === 0) {
        toast.success(message)
        setTimeout(() => {
          window.location = `${blessing.base_url}/user`
        }, 1000)
      } else {
        this.infoMsg = ''
        this.warningMsg = message
        this.$refs.captcha.refresh()
        this.pending = false
      }
    },
  },
}
</script>
