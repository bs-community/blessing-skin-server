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
        <div class="form-group has-feedback">
            <input
                v-model="password"
                type="password"
                class="form-control"
                :placeholder="$t('auth.password')"
                ref="password"
            >
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input
                v-model="confirm"
                type="password"
                class="form-control"
                :placeholder="$t('auth.repeat-pwd')"
                ref="confirm"
            >
            <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
        </div>

        <div
            class="form-group has-feedback"
            :title="$t('auth.nickname-intro')"
            data-placement="top"
            data-toggle="tooltip"
        >
            <input
                v-model="nickname"
                type="text"
                class="form-control"
                :placeholder="$t('auth.nickname')"
                ref="nickname"
            >
            <span class="glyphicon glyphicon-pencil form-control-feedback"></span>
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
                    alt="CAPTCHA"
                    :title="$t('auth.change-captcha')"
                    @click="refreshCaptcha"
                    data-placement="top"
                    data-toggle="tooltip"
                >
            </div>
        </div>

        <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
        <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

        <div class="row">
            <div class="col-xs-8">
                <a :href="`${baseUrl}/auth/login`" class="text-center" v-t="'auth.login-link'"></a>
            </div>
            <div class="col-xs-4">
                <button v-if="pending" disabled class="btn btn-primary btn-block btn-flat">
                    <i class="fa fa-spinner fa-spin"></i> {{ $t('auth.registering') }}
                </button>
                <button
                    v-else
                    @click.prevent="submit"
                    class="btn btn-primary btn-block btn-flat"
                >{{ $t('auth.register-button') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
import { swal } from '../../js/notify';

export default {
    name: 'Register',
    props: {
        baseUrl: {
            default: blessing.base_url
        }
    },
    data: () => ({
        email: '',
        password: '',
        confirm: '',
        nickname: '',
        captcha: '',
        time: Date.now(),
        infoMsg: '',
        warningMsg: '',
        pending: false
    }),
    methods: {
        async submit() {
            const {
                email, password, confirm, nickname, captcha
            } = this;

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

            if (!password) {
                this.infoMsg = this.$t('auth.emptyPassword');
                this.$refs.password.focus();
                return;
            }

            if (password.length < 8 || password.length > 32) {
                this.infoMsg = this.$t('auth.invalidPassword');
                this.$refs.password.focus();
                return;
            }

            if (password !== confirm) {
                this.infoMsg = this.$t('auth.invalidConfirmPwd');
                this.$refs.confirm.focus();
                return;
            }

            if (!nickname) {
                this.infoMsg = this.$t('auth.emptyNickname');
                this.$refs.nickname.focus();
                return;
            }

            if (!captcha) {
                this.infoMsg = this.$t('auth.emptyCaptcha');
                this.$refs.captcha.focus();
                return;
            }

            this.pending = true;
            const { errno, msg } = await this.$http.post(
                '/auth/register',
                { email, password, nickname, captcha }
            );
            if (errno === 0) {
                swal({ type: 'success', html: msg });
                setTimeout(() => {
                    window.location = `${blessing.base_url}/user`;
                }, 1000);
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
