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
          <h4 v-t="'user.player.add-player'" class="modal-title" />
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
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
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button class="btn btn-default" data-dismiss="modal">
            {{ $t('general.close') }}
          </button>
          <button class="btn btn-primary" data-test="addPlayer" @click="addPlayer">
            {{ $t('general.submit') }}
          </button>
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
        { name: this.name }
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
