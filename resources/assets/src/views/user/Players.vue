<template>
  <div>
    <portal selector="#players-list" :disabled="disablePortal">
      <div class="card card-primary">
        <div class="card-body table-responsive p-0">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>PID</th>
                <th v-t="'general.player.player-name'" />
                <th v-t="'user.player.operation'" />
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(player, index) in players"
                :key="player.pid"
                class="player"
                :class="{ 'player-selected': player.pid === selected }"
                @click="preview(player)"
              >
                <td class="pid">{{ player.pid }}</td>
                <td class="player-name">{{ player.name }}</td>
                <td>
                  <button class="btn btn-default" @click="changeName(player)">
                    {{ $t('user.player.edit-pname') }}
                  </button>
                  <button
                    class="btn btn-warning"
                    data-toggle="modal"
                    data-target="#modal-clear-texture"
                  >
                    {{ $t('user.player.delete-texture') }}
                  </button>
                  <button class="btn btn-danger" @click="deletePlayer(player, index)">
                    {{ $t('user.player.delete-player') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <button class="btn btn-primary" data-toggle="modal" data-target="#modal-add-player">
            <i class="fas fa-plus" aria-hidden="true" />&nbsp;{{ $t('user.player.add-player') }}
          </button>
        </div>
      </div>
    </portal>

    <portal selector="#previewer" :disabled="disablePortal">
      <previewer
        v-if="using3dPreviewer"
        :skin="skinUrl"
        :cape="capeUrl"
        :model="model"
        title="user.player.player-info"
      >
        <template #footer>
          <button class="btn btn-default" data-test="to2d" @click="togglePreviewer">
            {{ $t('user.switch2dPreview') }}
          </button>
        </template>
      </previewer>
      <div v-else class="card">
        <div class="card-header card-outline">
          <!-- eslint-disable-next-line vue/no-v-html -->
          <h3 class="card-title" v-html="$t('user.player.player-info')" />
        </div>
        <div class="card-body">
          <div id="preview-2d">
            <p>
              {{ $t('general.skin') }}
              <a v-if="preview2d.skin" :href="`${baseUrl}/skinlib/show/${preview2d.skin}`">
                <img
                  class="skin2d"
                  :src="`${baseUrl}/preview/${preview2d.skin}/64`"
                >
              </a>
              <span v-else v-t="'user.player.texture-empty'" class="skin2d" />
            </p>

            <p>
              {{ $t('general.cape') }}
              <a v-if="preview2d.cape" :href="`${baseUrl}/skinlib/show/${preview2d.cape}`">
                <img
                  class="skin2d"
                  :src="`${baseUrl}/preview/${preview2d.cape}/64`"
                >
              </a>
              <span v-else v-t="'user.player.texture-empty'" class="skin2d" />
            </p>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-default" @click="togglePreviewer">
            {{ $t('user.switch3dPreview') }}
          </button>
        </div>
      </div>
    </portal>

    <portal selector="#modals" :disabled="disablePortal">
      <add-player-dialog @add="fetchPlayers" />

      <modal
        id="modal-clear-texture"
        :title="$t('user.chooseClearTexture')"
        @confirm="clearTexture"
      >
        <label class="form-group">
          <input v-model="clear.skin" type="checkbox"> {{ $t('general.skin') }}
        </label>
        <br>
        <label class="form-group">
          <input v-model="clear.cape" type="checkbox"> {{ $t('general.cape') }}
        </label>
      </modal>
    </portal>
  </div>
</template>

<script>
import Modal from '../../components/Modal.vue'
import Portal from '../../components/Portal'
import AddPlayerDialog from '../../components/AddPlayerDialog.vue'
import emitMounted from '../../components/mixins/emitMounted'
import { showModal, toast } from '../../scripts/notify'
import { truthy } from '../../scripts/validators'

export default {
  name: 'Players',
  components: {
    AddPlayerDialog,
    Modal,
    Portal,
    Previewer: () => import('../../components/Previewer.vue'),
  },
  mixins: [
    emitMounted,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      players: [],
      selected: 0,
      using3dPreviewer: true,
      skinUrl: '',
      capeUrl: '',
      model: 'steve',
      preview2d: {
        skin: 0,
        cape: 0,
      },
      clear: {
        skin: false,
        cape: false,
      },
      disablePortal: process.env.NODE_ENV === 'test',
    }
  },
  async beforeMount() {
    await this.fetchPlayers()
    if (this.players.length === 1) {
      this.preview(this.players[0])
    }
  },
  methods: {
    async fetchPlayers() {
      this.players = (await this.$http.get('/user/player/list')).data
    },
    togglePreviewer() {
      this.using3dPreviewer = !this.using3dPreviewer
    },
    async preview(player) {
      this.selected = player.pid

      this.preview2d.skin = player.tid_skin
      this.preview2d.cape = player.tid_cape

      if (player.tid_skin) {
        const { data: skin } = await this.$http.get(`/skinlib/info/${player.tid_skin}`)
        this.skinUrl = `${this.baseUrl}/textures/${skin.hash}`
        this.model = skin.type
      } else {
        this.skinUrl = ''
        this.model = 'steve'
      }
      if (player.tid_cape) {
        const { data: cape } = await this.$http.get(`/skinlib/info/${player.tid_cape}`)
        this.capeUrl = `${this.baseUrl}/textures/${cape.hash}`
      } else {
        this.capeUrl = ''
      }
    },
    async changeName(player) {
      let value
      try {
        ({ value } = await showModal({
          mode: 'prompt',
          text: this.$t('user.changePlayerName'),
          input: player.name,
          validator: truthy(this.$t('user.emptyPlayerName')),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        `/user/player/rename/${player.pid}`,
        { name: value },
      )
      if (code === 0) {
        toast.success(message)
        player.name = value
      } else {
        toast.error(message)
      }
    },
    async clearTexture() {
      if (Object.values(this.clear).every(value => !value)) {
        toast.error(this.$t('user.noClearChoice'))
        return
      }

      const { code, message } = await this.$http.post(
        `/user/player/texture/clear/${this.selected}`,
        this.clear,
      )
      if (code === 0) {
        toast.success(message)
        const player = this.players.find(({ pid }) => pid === this.selected)
        Object.keys(this.clear)
          .filter(type => this.clear[type])
          .forEach(type => (player[`tid_${type}`] = 0))
      } else {
        toast.error(message)
      }
    },
    async deletePlayer(player, index) {
      try {
        await showModal({
          title: this.$t('user.deletePlayer'),
          text: this.$t('user.deletePlayerNotice'),
          okButtonType: 'danger',
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(`/user/player/delete/${player.pid}`)
      if (code === 0) {
        this.$delete(this.players, index)
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.player
  cursor pointer
  border-bottom 1px solid #f4f4f4

  .pid, .player-name
    padding-top 13px

.player:last-child
  border-bottom none

.player-selected
  background-color #f5f5f5

.skin2d
  float right
  max-height 64px
  width 64px
  font-size 16px

#preview-2d > p
  height 64px
  line-height 64px
</style>
