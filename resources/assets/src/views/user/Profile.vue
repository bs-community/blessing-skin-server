<template>
  <section class="content">
    <email-verification />
    <div class="row">
      <div class="col-md-6">
        <div v-once class="box box-primary">
          <div class="box-header with-border">
            <h3 v-t="'user.profile.avatar.title'" class="box-title" />
          </div><!-- /.box-header -->
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div class="box-body" v-html="$t('user.profile.avatar.notice')" /><!-- /.box-body -->
          <div class="box-footer">
            <el-button type="primary" data-test="resetAvatar" @click="resetAvatar">
              {{ $t('user.resetAvatar') }}
            </el-button>
          </div>
        </div>

        <form class="box box-warning" data-test="changePassword" @submit.prevent="changePassword">
          <div class="box-header with-border">
            <h3 v-t="'user.profile.password.title'" class="box-title" />
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="form-group">
              <label v-t="'user.profile.password.old'" />
              <input
                v-model="oldPassword"
                type="password"
                class="form-control"
                required
              >
            </div>

            <div class="form-group">
              <label v-t="'user.profile.password.new'" />
              <input
                v-model="newPassword"
                type="password"
                class="form-control"
                required
                minlength="8"
                maxlength="32"
              >
            </div>

            <div class="form-group">
              <label v-t="'user.profile.password.confirm'" />
              <input
                ref="confirmPassword"
                v-model="confirmPassword"
                type="password"
                class="form-control"
                required
                minlength="8"
                maxlength="32"
              >
            </div>
          </div><!-- /.box-body -->
          <div class="box-footer">
            <el-button type="primary" native-type="submit">
              {{ $t('user.profile.password.button') }}
            </el-button>
          </div>
        </form><!-- /.box -->
      </div>
      <div class="col-md-6">
        <form class="box box-primary" data-test="changeNickName" @submit.prevent="changeNickName">
          <div class="box-header with-border">
            <h3 v-t="'user.profile.nickname.title'" class="box-title" />
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="form-group has-feedback">
              <input
                v-model="nickname"
                type="text"
                class="form-control"
                :placeholder="$t('user.profile.nickname.rule')"
                required
              >
              <span class="glyphicon glyphicon-user form-control-feedback" />
            </div>
          </div><!-- /.box-body -->
          <div class="box-footer">
            <el-button type="primary" native-type="submit">
              {{ $t('general.submit') }}
            </el-button>
          </div>
        </form>

        <form class="box box-warning" data-test="changeEmail" @submit.prevent="changeEmail">
          <div class="box-header with-border">
            <h3 v-t="'user.profile.email.title'" class="box-title" />
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="form-group has-feedback">
              <input
                ref="email"
                v-model="email"
                type="email"
                class="form-control"
                :placeholder="$t('user.profile.email.new')"
                required
              >
              <span class="glyphicon glyphicon-envelope form-control-feedback" />
            </div>
            <div class="form-group has-feedback">
              <input
                ref="currentPassword"
                v-model="currentPassword"
                type="password"
                class="form-control"
                :placeholder="$t('user.profile.email.password')"
                required
              >
              <span class="glyphicon glyphicon-lock form-control-feedback" />
            </div>
          </div><!-- /.box-body -->
          <div class="box-footer">
            <el-button type="primary" native-type="submit">
              {{ $t('user.profile.email.button') }}
            </el-button>
          </div>
        </form>

        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 v-t="'user.profile.delete.title'" class="box-title" />
          </div><!-- /.box-header -->
          <div class="box-body">
            <template v-if="isAdmin">
              <p v-t="'user.profile.delete.admin'" />
              <el-button type="danger" disabled>
                {{ $t('user.profile.delete.button') }}
              </el-button>
            </template>
            <template v-else>
              <p v-t="{ path: 'user.profile.delete.notice', args: { site: siteName } }" />
              <el-button type="danger" data-toggle="modal" data-target="#modal-delete-account">
                {{ $t('user.profile.delete.button') }}
              </el-button>
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
      <form class="modal-dialog" data-test="deleteAccount" @submit.prevent="deleteAccount">
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
            <h4 v-t="'user.profile.delete.modal-title'" class="modal-title" />
          </div>
          <div class="modal-body">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-once v-html="nl2br($t('user.profile.delete.modal-notice'))" />
            <br>
            <input
              v-model="deleteConfirm"
              type="password"
              class="form-control"
              :placeholder="$t('user.profile.delete.password')"
              required
            >
            <br>
          </div>
          <div class="modal-footer">
            <button
              v-t="'general.close'"
              type="button"
              class="btn btn-outline"
              data-dismiss="modal"
            />
            <button
              v-t="'general.submit'"
              type="submit"
              class="btn btn-outline"
            />
          </div>
        </div><!-- /.modal-content -->
      </form><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </section><!-- /.content -->
</template>

<script>
import EmailVerification from '../../components/EmailVerification.vue'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'Profile',
  components: {
    EmailVerification,
  },
  mixins: [
    emitMounted,
  ],
  data: () => ({
    oldPassword: '',
    newPassword: '',
    confirmPassword: '',
    nickname: '',
    email: '',
    currentPassword: '',
    deleteConfirm: '',
    siteName: blessing.site_name,
    isAdmin: blessing.extra.admin,
  }),
  methods: {
    nl2br: str => str.replace(/\n/g, '<br>'),
    async resetAvatar() {
      try {
        await this.$confirm(this.$t('user.resetAvatarConfirm'))
      } catch {
        return
      }

      const { message } = await this.$http.post(
        '/user/profile/avatar',
        { tid: 0 }
      )
      this.$message.success(message)
      Array.from(document.querySelectorAll('[alt="User Image"]'))
        .forEach(el => (el.src += `?${new Date().getTime()}`))
    },
    async changePassword() {
      const {
        oldPassword, newPassword, confirmPassword,
      } = this

      if (newPassword !== confirmPassword) {
        this.$message.error(this.$t('auth.invalidConfirmPwd'))
        this.$refs.confirmPassword.focus()
        return
      }

      const { code, message } = await this.$http.post(
        '/user/profile?action=password',
        { current_password: oldPassword, new_password: newPassword }
      )
      if (code === 0) {
        await this.$alert(message)
        return (window.location = `${blessing.base_url}/auth/login`)
      }
      return this.$alert(message, { type: 'warning' })
    },
    async changeNickName() {
      const { nickname } = this

      try {
        await this.$confirm(this.$t('user.changeNickName', { new_nickname: nickname }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/profile?action=nickname',
        { new_nickname: nickname }
      )
      if (code === 0) {
        Array.from(document.querySelectorAll('.nickname'))
          .forEach(el => (el.textContent = nickname))
        return this.$message.success(message)
      }
      return this.$alert(message, { type: 'warning' })
    },
    async changeEmail() {
      const { email } = this

      try {
        await this.$confirm(this.$t('user.changeEmail', { new_email: email }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/user/profile?action=email',
        { new_email: email, password: this.currentPassword }
      )
      if (code === 0) {
        await this.$message.success(message)
        return (window.location = `${blessing.base_url}/auth/login`)
      }
      return this.$alert(message, { type: 'warning' })
    },
    async deleteAccount() {
      const { deleteConfirm: password } = this

      const { code, message } = await this.$http.post(
        '/user/profile?action=delete',
        { password }
      )
      if (code === 0) {
        await this.$alert(message, { type: 'success' })
        window.location = `${blessing.base_url}/auth/login`
      } else {
        return this.$alert(message, { type: 'warning' })
      }
    },
  },
}
</script>
