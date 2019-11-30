<template>
  <div class="container-fluid">
    <email-verification />
    <div class="row">
      <div class="col-md-6">
        <div v-once class="card card-primary">
          <div class="card-header">
            <h3 v-t="'user.profile.avatar.title'" class="card-title" />
          </div>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <div class="card-body" v-html="$t('user.profile.avatar.notice')" />
          <div class="card-footer">
            <button class="btn btn-primary" data-test="resetAvatar" @click="resetAvatar">
              {{ $t('user.resetAvatar') }}
            </button>
          </div>
        </div>

        <form
          class="card card-warning"
          data-test="changePassword"
          @submit.prevent="changePassword"
        >
          <div class="card-header">
            <h3 v-t="'user.profile.password.title'" class="card-title" />
          </div>
          <div class="card-body">
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
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" type="submit">
              {{ $t('user.profile.password.button') }}
            </button>
          </div>
        </form>
      </div>
      <div class="col-md-6">
        <form
          class="card card-primary"
          data-test="changeNickName"
          @submit.prevent="changeNickName"
        >
          <div class="card-header">
            <h3 v-t="'user.profile.nickname.title'" class="card-title" />
          </div>
          <div class="card-body">
            <div class="form-group">
              <input
                v-model="nickname"
                type="text"
                class="form-control"
                :placeholder="$t('user.profile.nickname.rule')"
                required
              >
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" type="submit">
              {{ $t('general.submit') }}
            </button>
          </div>
        </form>

        <form
          class="card card-warning"
          data-test="changeEmail"
          @submit.prevent="changeEmail"
        >
          <div class="card-header">
            <h3 v-t="'user.profile.email.title'" class="card-title" />
          </div>
          <div class="card-body">
            <div class="form-group">
              <input
                ref="email"
                v-model="email"
                type="email"
                class="form-control"
                :placeholder="$t('user.profile.email.new')"
                required
              >
            </div>
            <div class="form-group">
              <input
                ref="currentPassword"
                v-model="currentPassword"
                type="password"
                class="form-control"
                :placeholder="$t('user.profile.email.password')"
                required
              >
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary" type="submit">
              {{ $t('user.profile.email.button') }}
            </button>
          </div>
        </form>

        <div class="card card-danger">
          <div class="card-header">
            <h3 v-t="'user.profile.delete.title'" class="card-title" />
          </div>
          <div class="card-body">
            <template v-if="isAdmin">
              <p v-t="'user.profile.delete.admin'" />
              <button class="btn btn-danger" disabled>
                {{ $t('user.profile.delete.button') }}
              </button>
            </template>
            <template v-else>
              <p v-t="{ path: 'user.profile.delete.notice', args: { site: siteName } }" />
              <button
                class="btn btn-danger"
                data-toggle="modal"
                data-target="#modal-delete-account"
              >
                {{ $t('user.profile.delete.button') }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <form data-test="deleteAccount" @submit.prevent="deleteAccount">
      <modal
        id="modal-delete-account"
        type="danger"
        :title="$t('user.profile.delete.modal-title')"
        flex-footer
      >
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
        <template #footer>
          <button
            v-t="'general.close'"
            type="button"
            class="btn btn-outline-light"
            data-dismiss="modal"
          />
          <button
            v-t="'general.submit'"
            type="submit"
            class="btn btn-outline-light"
          />
        </template>
      </modal>
    </form>
  </div>
</template>

<script>
import EmailVerification from '../../components/EmailVerification.vue'
import Modal from '../../components/Modal.vue'
import emitMounted from '../../components/mixins/emitMounted'
import { showModal } from '../../scripts/notify'

export default {
  name: 'Profile',
  components: {
    EmailVerification,
    Modal,
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
        await showModal({ text: this.$t('user.resetAvatarConfirm') })
      } catch {
        return
      }

      const { message } = await this.$http.post(
        '/user/profile/avatar',
        { tid: 0 },
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
        { current_password: oldPassword, new_password: newPassword },
      )
      await showModal({ mode: 'alert', text: message })
      if (code === 0) {
        return (window.location = `${blessing.base_url}/auth/login`)
      }
    },
    async changeNickName() {
      const { nickname } = this

      const { code, message } = await this.$http.post(
        '/user/profile?action=nickname',
        { new_nickname: nickname },
      )
      if (code === 0) {
        Array
          .from(document.querySelectorAll('[data-mark="nickname"]'))
          .forEach(el => (el.textContent = nickname))
        return this.$message.success(message)
      }
      showModal({ mode: 'alert', text: message })
    },
    async changeEmail() {
      const { email } = this

      const { code, message } = await this.$http.post(
        '/user/profile?action=email',
        { new_email: email, password: this.currentPassword },
      )
      if (code === 0) {
        await this.$message.success(message)
        return (window.location = `${blessing.base_url}/auth/login`)
      }
      showModal({ mode: 'alert', text: message })
    },
    async deleteAccount() {
      const { deleteConfirm: password } = this

      const { code, message } = await this.$http.post(
        '/user/profile?action=delete',
        { password },
      )
      await showModal({ mode: 'alert', text: message })
      if (code === 0) {
        window.location = `${blessing.base_url}/auth/login`
      }
    },
  },
}
</script>
