<template>
  <form @submit.prevent="reset">
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

    <div class="alert alert-info" :class="{ 'd-none': !infoMsg }">
      <i class="icon fas fa-info" />
      {{ infoMsg }}
    </div>
    <div class="alert alert-warning" :class="{ 'd-none': !warningMsg }">
      <i class="icon fas fa-exclamation-triangle" />
      {{ warningMsg }}
    </div>

    <button
      class="btn btn-primary float-right"
      type="submit"
      :disabled="pending"
    >
      <template v-if="pending">
        <i class="fa fa-spinner fa-spin" /> {{ $t('auth.resetting') }}
      </template>
      <span v-else>{{ $t('auth.reset-button') }}</span>
    </button>
  </form>
</template>

<script>
import emitMounted from '../../components/mixins/emitMounted'
import { toast } from '../../scripts/notify'

export default {
  name: 'Reset',
  mixins: [
    emitMounted,
  ],
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

      if (password !== confirm) {
        this.infoMsg = this.$t('auth.invalidConfirmPwd')
        this.$refs.confirm.focus()
        return
      }

      this.pending = true
      const { code, message } = await this.$http.post(
        `/auth/reset/${this.uid}${location.search}`,
        { password },
      )
      if (code === 0) {
        toast.success(message)
        setTimeout(() => {
          window.location = `${blessing.base_url}/auth/login`
        }, 2000)
      } else {
        this.infoMsg = ''
        this.warningMsg = message
        this.pending = false
      }
    },
  },
}
</script>
