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
      const { errno, msg } = await this.$http.post('/user/email-verification')
      if (errno === 0) {
        this.$message.success(msg)
      } else {
        this.$message.error(msg)
      }
      this.pending = false
    },
  },
}
</script>
