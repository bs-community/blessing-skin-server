<template>
  <div v-if="!verified" class="callout callout-info">
    <h4><i class="fas fa-envelope" /> {{ $t('user.verification.title') }}</h4>
    <p>
      {{ $t('user.verification.message') }}
      <span v-if="pending">
        <i class="fas fa-spin fa-spinner" />
        {{ $t('user.verification.sending') }}
      </span>
      <a v-else href="#" @click="resend">
        {{ $t('user.verification.resend') }}
      </a>
    </p>
  </div>
</template>

<script>
import { toast } from '../scripts/notify'

export default {
  name: 'EmailVerification',
  data() {
    return {
      verified: !blessing.extra.unverified,
      pending: false,
    }
  },
  methods: {
    async resend() {
      this.pending = true
      const { code, message } = await this.$http.post('/user/email-verification')
      if (code === 0) {
        toast.success(message)
      } else {
        toast.error(message)
      }
      this.pending = false
    },
  },
}
</script>
