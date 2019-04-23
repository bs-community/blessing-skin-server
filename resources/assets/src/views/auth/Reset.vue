<template>
  <form @submit.prevent="reset">
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

    <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
    <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

    <div class="row">
      <div class="col-xs-7" />
      <div class="col-xs-5">
        <el-button
          type="primary"
          native-type="submit"
          :disabled="pending"
          class="auth-btn"
        >
          <template v-if="pending">
            <i class="fa fa-spinner fa-spin" /> {{ $t('auth.resetting') }}
          </template>
          <span v-else>{{ $t('auth.reset-button') }}</span>
        </el-button>
      </div>
    </div>
  </form>
</template>

<script>
export default {
  name: 'Reset',
  data() {
    return {
      uid: +this.$route[1],
      password: '',
      confirm: '',
      infoMsg: '',
      warningMsg: '',
      pending: false,
    }
  },
  methods: {
    async reset() {
      const { password, confirm } = this

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

      this.pending = true
      const { code, message } = await this.$http.post(
        `/auth/reset/${this.uid}${location.search}`,
        { password }
      )
      if (code === 0) {
        this.$message.success(message)
        window.location = `${blessing.base_url}/auth/login`
      } else {
        this.infoMsg = ''
        this.warningMsg = message
        this.pending = false
      }
    },
  },
}
</script>
