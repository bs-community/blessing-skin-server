<template>
    <form>
        <div class="form-group has-feedback">
            <input
                v-model="password"
                type="password"
                class="form-control"
                ref="password"
                :placeholder="$t('auth.password')"
            >
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input
                v-model="confirm"
                type="password"
                class="form-control"
                ref="confirm"
                :placeholder="$t('auth.repeat-pwd')"
            >
            <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
        </div>

        <div class="callout callout-info" :class="{ hide: !infoMsg }">{{ infoMsg }}</div>
        <div class="callout callout-warning" :class="{ hide: !warningMsg }">{{ warningMsg }}</div>

        <div class="row">
            <div class="col-xs-8">
            </div>
            <div class="col-xs-4">
                <button v-if="pending" disabled class="btn btn-primary btn-block btn-flat">
                    <i class="fa fa-spinner fa-spin"></i> {{ $t('auth.resetting') }}
                </button>
                <button v-else @click.prevent="reset" class="btn btn-primary btn-block btn-flat">
                    {{ $t('auth.reset-button') }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
import { swal } from '../../js/notify';

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
        };
    },
    methods: {
        async reset() {
            const { password, confirm } = this;

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

            this.pending = true;
            const { errno, msg } = await this.$http.post(
                `/auth/reset/${this.uid}${location.search}`,
                { password }
            );
            if (errno === 0) {
                await swal({ type: 'success', text: msg });
                window.location = `${blessing.base_url}/auth/login`;
            } else {
                this.infoMsg = '';
                this.warningMsg = msg;
                this.pending = false;
            }
        }
    }
};
</script>
