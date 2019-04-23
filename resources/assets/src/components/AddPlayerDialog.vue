<template>
  <div
    id="modal-add-player"
    class="modal fade"
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
          <h4 v-t="'user.player.add-player'" class="modal-title" />
        </div>
        <div class="modal-body">
          <table class="table">
            <tbody>
              <tr>
                <td v-t="'general.player.player-name'" class="key" />
                <td class="value">
                  <el-input v-model="name" type="text" />
                </td>
              </tr>
            </tbody>
          </table>

          <div class="callout callout-info">
            <ul style="padding: 0 0 0 20px; margin: 0;">
              <li>{{ rule }}</li>
              <li>{{ length }}</li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <el-button data-dismiss="modal">{{ $t('general.close') }}</el-button>
          <el-button type="primary" data-test="addPlayer" @click="addPlayer">
            {{ $t('general.submit') }}
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddPlayerDialog',
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
        { player_name: this.name }
      )
      if (code === 0) {
        $('#modal-add-player').modal('hide')
        this.$message.success(message)
        this.$emit('add')
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>
