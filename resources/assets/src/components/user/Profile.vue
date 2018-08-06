<template>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div v-once class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" v-t="'user.profile.avatar.title'"></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body" v-t="'user.profile.avatar.notice'"></div><!-- /.box-body -->
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title" v-t="'user.profile.password.title'"></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group">
                            <label v-t="'user.profile.password.old'"></label>
                            <input
                                type="password"
                                class="form-control"
                                v-model="oldPassword"
                                ref="oldPassword"
                            >
                        </div>

                        <div class="form-group">
                            <label v-t="'user.profile.password.new'"></label>
                            <input
                                type="password"
                                class="form-control"
                                v-model="newPassword"
                                ref="newPassword"
                            >
                        </div>

                        <div class="form-group">
                            <label v-t="'user.profile.password.confirm'"></label>
                            <input
                                type="password"
                                class="form-control"
                                v-model="confirmPassword"
                                ref="confirmPassword"
                            >
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button
                            @click="changePassword"
                            class="btn btn-primary"
                            v-t="'user.profile.password.button'"
                            data-test="changePassword"
                        ></button>
                    </div>
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" v-t="'user.profile.nickname.title'"></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            <input
                                v-model="nickname"
                                type="text"
                                class="form-control"
                                :placeholder="$t('user.profile.nickname.rule')"
                                ref="nickname"
                            >
                            <span class="glyphicon glyphicon-user form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button
                            @click="changeNickName"
                            class="btn btn-primary"
                            v-t="'general.submit'"
                            data-test="changeNickName"
                        ></button>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title" v-t="'user.profile.email.title'"></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group has-feedback">
                            <input
                                v-model="email"
                                type="email"
                                class="form-control"
                                :placeholder="$t('user.profile.email.new')"
                                ref="email"
                            >
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input
                                v-model="currentPassword"
                                type="password"
                                class="form-control"
                                :placeholder="$t('user.profile.email.password')"
                                ref="currentPassword"
                            >
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button
                            @click="changeEmail"
                            class="btn btn-warning"
                            v-t="'user.profile.email.button'"
                            data-test="changeEmail"
                        ></button>
                    </div>
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title" v-t="'user.profile.delete.title'"></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <template v-if="isAdmin">
                            <p v-t="'user.profile.delete.admin'"></p>
                            <button class="btn btn-danger" disabled v-t="'user.profile.delete.button'"></button>
                        </template>
                        <template v-else>
                            <p v-t="{ path: 'user.profile.delete.notice', args: { site: siteName } }"></p>
                            <button
                                class="btn btn-danger"
                                data-toggle="modal"
                                data-target="#modal-delete-account"
                                v-t="'user.profile.delete.button'"
                            ></button>
                        </template>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>

        <div
            id="modal-delete-account"
            class="modal modal-danger fade"
            tabindex="-1"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" v-t="'user.profile.delete.modal-title'"></h4>
                    </div>
                    <div class="modal-body">
                        <div v-once v-html="nl2br($t('user.profile.delete.modal-notice'))"></div>
                        <br />
                        <input
                            type="password"
                            class="form-control"
                            v-model="deleteConfirm"
                            :placeholder="$t('user.profile.delete.password')"
                        >
                        <br />
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline"
                            data-dismiss="modal"
                            v-t="'general.close'"
                        ></button>
                        <a
                            @click="deleteAccount"
                            class="btn btn-outline"
                            v-t="'general.submit'"
                            data-test="deleteAccount"
                        ></a>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </section><!-- /.content -->
</template>

<script>
import toastr from 'toastr';
import { swal } from '../../js/notify';

export default {
    name: 'Profile',
    data: () => ({
        oldPassword: '',
        newPassword: '',
        confirmPassword: '',
        nickname: '',
        email: '',
        currentPassword: '',
        deleteConfirm: '',
    }),
    computed: {
        siteName: () => blessing.site_name,
        isAdmin: () => __bs_data__.admin
    },
    methods: {
        nl2br: str => str.replace(/\n/g, '<br>'),
        async changePassword() {
            const {
                oldPassword, newPassword, confirmPassword
            } = this;

            if (!oldPassword) {
                toastr.info(this.$t('user.emptyPassword'));
                this.$refs.oldPassword.focus();
                return;
            }

            if (!newPassword) {
                toastr.info(this.$t('user.emptyNewPassword'));
                this.$refs.newPassword.focus();
                return;
            }

            if (!confirmPassword) {
                toastr.info(this.$t('auth.emptyConfirmPwd'));
                this.$refs.confirmPassword.focus();
                return;
            }

            if (newPassword !== confirmPassword) {
                toastr.info(this.$t('auth.invalidConfirmPwd'));
                this.$refs.confirmPassword.focus();
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/user/profile?action=password',
                { current_password: oldPassword, new_password: newPassword }
            );
            if (errno === 0) {
                await swal({ type: 'success', text: msg });
                return window.location = `${blessing.base_url}/auth/login`;
            } else {
                return swal({ type: 'warning', text: msg });
            }
        },
        async changeNickName() {
            const { nickname } = this;

            if (!nickname) {
                return swal({ type: 'error', html: this.$t('user.emptyNewNickName') });
            }

            const { dismiss } = await swal({
                text: this.$t('user.changeNickName', { new_nickname: nickname }),
                type: 'question',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/user/profile?action=nickname',
                { new_nickname: nickname }
            );
            if (errno === 0) {
                $('.nickname').each(function () {
                    $(this).html(nickname);
                });
                return swal({ type: 'success', html: msg });
            } else {
                return swal({ type: 'warning', html: msg });
            }
        },
        async changeEmail() {
            const { email } = this;

            if (!email) {
                return swal({ type: 'error', html: this.$t('user.emptyNewEmail') });
            }

            if (!/\S+@\S+\.\S+/.test(email)) {
                return swal({ type: 'warning', html: this.$t('auth.invalidEmail') });
            }

            const { dismiss } = await swal({
                text: this.$t('user.changeEmail', { new_email: email }),
                type: 'question',
                showCancelButton: true
            });
            if (dismiss) {
                return;
            }

            const { errno, msg } = await this.$http.post(
                '/user/profile?action=email',
                { new_email: email, password: this.currentPassword }
            );
            if (errno === 0) {
                await swal({ type: 'success', text: msg });
                return window.location = `${blessing.base_url}/auth/login`;
            } else {
                return swal({ type: 'warning', text: msg });
            }
        },
        async deleteAccount() {
            const { deleteConfirm: password } = this;

            if (!password) {
                return swal({ type: 'warning', html: this.$t('user.emptyDeletePassword') });
            }

            const { errno, msg } = await this.$http.post(
                '/user/profile?action=delete',
                { password }
            );
            if (errno === 0) {
                await swal({
                    type: 'success',
                    html: msg
                });
                window.location = `${blessing.base_url}/auth/login`;
            } else {
                return swal({ type: 'warning', html: msg });
            }
        }
    }
};
</script>
