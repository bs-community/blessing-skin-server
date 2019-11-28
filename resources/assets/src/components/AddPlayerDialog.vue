<template>
  <modal
    id="modal-add-player"
    :title="$t('user.player.add-player')"
    :ok-button-text="$t('general.submit')"
    flex-footer
    @confirm="addPlayer"
  >
    <table class="table">
      <tbody>
        <tr>
          <td v-t="'general.player.player-name'" class="key" />
          <td class="value">
            <input v-model="name" class="form-control" type="text">
          </td>
        </tr>
      </tbody>
    </table>
    <div class="callout callout-info">
      <ul class="m-0 p-0 pl-3">
        <li>{{ rule }}</li>
        <li>{{ length }}</li>
      </ul>
    </div>
  </modal>
</template>

<script>
import Modal from './Modal.vue'

export default {
  name: 'AddPlayerDialog',
  components: {
    Modal,
  },
  data() {
    return {
      name: '',
      rule: blessing.extra.rule,
      length: blessing.extra.length,
    }
  },
  methods: {
    async addPlayer() {
      const { code, message } = await this.$http.post(
        '/user/player/add',
        { name: this.name },
      )
      if (code === 0) {
        this.$message.success(message)
        this.$emit('add')
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>
