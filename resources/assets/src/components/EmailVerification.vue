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
        this.$message.success(message)
      } else {
        this.$message.error(message)
      }
      this.pending = false
    },
  },
}
</script>
