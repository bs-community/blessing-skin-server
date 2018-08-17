<template>
    <div v-if="!verified" class="callout callout-info">
        <h4><i class="fa fa-envelope"></i> {{ $t('user.verification.title') }}</h4>
        <p>{{ $t('user.verification.message') }}
            <span v-if="pending">
                <i class="fa fa-spin fa-spinner"></i>
                {{ $t('user.verification.sending') }}
            </span>
            <a v-else @click="resend" href="#">
                {{ $t('user.verification.resend') }}
            </a>
        </p>
    </div>
</template>

<script>
import { swal } from '../../js/notify';

export default {
    name: 'EmailVerification',
    data() {
        return {
            verified: !__bs_data__.unverified,
            pending: false,
        };
    },
    methods: {
        async resend() {
            this.pending = true;
            const { errno, msg } = await this.$http.post('/user/email-verification');
            swal({ type: errno === 0 ? 'success' : 'warning', text: msg });
            this.pending = false;
        }
    }
};
</script>
