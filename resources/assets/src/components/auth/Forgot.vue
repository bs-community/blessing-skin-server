<template>
    <form>
        <div class="form-group has-feedback">
            <input
                v-model="email"
                type="email"
                class="form-control"
                :placeholder="$t('auth.email')"
                ref="email"
            >
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>

        <div class="row">
            <div class="col-xs-8">
                <div class="form-group has-feedback">
                    <input
                        v-model="captcha"
                        type="text"
                        class="form-control"
                        :placeholder="$t('auth.captcha')"
                        ref="captcha"
                    >
                </div>
            </div>
            <div class="col-xs-4">
                <img
                    class="pull-right captcha"
                    :src="`${baseUrl}/auth/captcha?v=${time}`"
                    @click="refreshCaptcha"
                    alt="CAPTCHA"
                    :title="$t('auth.change-captcha')"
                    data-placement="top"
                    data-toggle="tooltip"
                >
            </div>
        </div>

        <div class="callout callout-success" :class="{ hide: !successMsg }">{{ successMsg }}</div>
        <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
        <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

        <div class="row">
            <div class="col-xs-8">
                <a :href="`${baseUrl}/auth/login`" class="text-center" v-t="'auth.forgot.login-link'"></a>
            </div>
            <div class="col-xs-4">
                <button v-if="pending" disabled class="btn btn-primary btn-block btn-flat">
                    <i class="fa fa-spinner fa-spin"></i> {{ $t('auth.sending') }}
                </button>
                <button v-else @click.prevent="submit" class="btn btn-primary btn-block btn-flat">{{ $t('auth.forgot.button') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'Forgot',
    props: {
        baseUrl: {
            default: blessing.base_url
        }
    },
    data: () => ({
        email: '',
        captcha: '',
        time: Date.now(),
        successMsg: '',
        infoMsg: '',
        warningMsg: '',
        pending: false
    }),
    methods: {
        async submit() {
            const { email, captcha } = this;

            if (!email) {
                this.infoMsg = this.$t('auth.emptyEmail');
                this.$refs.email.focus();
                return;
            }

            if (!/\S+@\S+\.\S+/.test(email)) {
                this.infoMsg = this.$t('auth.invalidEmail');
                this.$refs.email.focus();
                return;
            }

            if (!captcha) {
                this.infoMsg = this.$t('auth.emptyCaptcha');
                this.$refs.captcha.focus();
                return;
            }

            this.pending = true;
            const { errno, msg } = await this.$http.post(
                '/auth/forgot',
                { email, captcha }
            );
            if (errno === 0) {
                this.infoMsg = this.warningMsg = '';
                this.successMsg = msg;
                this.pending = false;
            } else {
                this.infoMsg = '';
                this.warningMsg = msg;
                this.refreshCaptcha();
                this.pending = false;
            }
        },
        refreshCaptcha() {
            this.time = Date.now();
        }
    }
};
</script>
