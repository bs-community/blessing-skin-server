<template>
  <div
    id="modal-use-as"
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
          <h4 v-t="'user.closet.use-as.title'" class="modal-title" />
        </div>
        <div class="modal-body">
          <template v-if="players.length !== 0">
            <div v-for="player in players" :key="player.pid" class="player-item">
              <label class="model-label" :for="player.pid">
                <input
                  v-model="selected"
                  type="radio"
                  name="player"
                  :value="player.pid"
                >
                <img :src="avatarUrl(player)" width="35" height="35">
                <span>{{ player.name }}</span>
              </label>
            </div>
          </template>
          <p v-else v-t="'user.closet.use-as.empty'" />
        </div>
        <div class="modal-footer">
          <a
            v-if="allowAdd"
            v-t="'user.closet.use-as.add'"
            data-toggle="modal"
            data-target="#modal-add-player"
            class="el-button pull-left"
          />
          <el-button type="primary" data-test="submit" @click="submit">
            {{ $t('general.submit') }}
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApplyToPlayerDialog',
  props: {
    skin: Number,
    cape: Number,
    allowAdd: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      players: [],
      selected: 0,
    }
  },
  methods: {
    async fetchList() {
      this.players = await this.$http.get('/user/player/list')
    },
    async submit() {
      if (!this.selected) {
        return this.$message.info(this.$t('user.emptySelectedPlayer'))
      }

      if (!this.skin && !this.cape) {
        return this.$message.info(this.$t('user.emptySelectedTexture'))
      }

      const { code, message } = await this.$http.post(
        '/user/player/set',
        {
          pid: this.selected,
          tid: {
            skin: this.skin || undefined,
            cape: this.cape || undefined,
          },
        }
      )
      if (code === 0) {
        this.$message.success(message)
        $('#modal-use-as').modal('hide')
      } else {
        this.$message.warning(message)
      }
    },
    avatarUrl(player) {
      return `${blessing.base_url}/avatar/35/${player.tid_skin}`
    },
  },
}
</script>

<style lang="stylus">
.player-item:not(:nth-child(1))
  margin-top 10px
</style>
