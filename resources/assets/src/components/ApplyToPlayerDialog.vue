<template>
  <modal
    id="modal-use-as"
    ref="modal"
    :title="$t('user.closet.use-as.title')"
    :ok-button-text="$t('general.submit')"
    flex-footer
  >
    <template v-if="players.length !== 0">
      <div class="form-group">
        <input
          v-model="search"
          type="text"
          class="form-control"
          :placeholder="$t('user.typeToSearch')"
        >
      </div>
      <button
        v-for="player in filteredPlayers"
        :key="player.pid"
        class="btn btn-block btn-outline-info text-left"
        @click="submit(player.pid)"
      >
        <img :src="avatarUrl(player)" width="45" height="45">&nbsp;
        <span>{{ player.name }}</span>
      </button>
    </template>
    <p v-else v-t="'user.closet.use-as.empty'" />
    <template #footer>
      <a
        v-if="allowAdd"
        v-t="'user.closet.use-as.add'"
        data-toggle="modal"
        data-target="#modal-add-player"
        class="btn btn-default"
        href="#"
      />
      <button class="btn btn-default" data-dismiss="modal">
        {{ $t('general.cancel') }}
      </button>
    </template>
  </modal>
</template>

<script>
import $ from 'jquery'
import Modal from './Modal.vue'
import { toast } from '../scripts/notify'

export default {
  name: 'ApplyToPlayerDialog',
  components: {
    Modal,
  },
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
      search: '',
    }
  },
  computed: {
    filteredPlayers() {
      return this.players.filter(player => player.name.includes(this.search))
    },
  },
  mounted() {
    this.fetchList()
  },
  methods: {
    async fetchList() {
      this.players = (await this.$http.get('/user/player/list')).data
    },
    async submit(selected) {
      if (!this.skin && !this.cape) {
        toast.info(this.$t('user.emptySelectedTexture'))
        return
      }

      const { code, message } = await this.$http.post(
        `/user/player/set/${selected}`,
        {
          skin: this.skin || undefined,
          cape: this.cape || undefined,
        },
      )
      if (code === 0) {
        toast.success(message)
        $('#modal-use-as').modal('hide')
      } else {
        toast.error(message)
      }
    },
    avatarUrl(player) {
      return `${blessing.base_url}/avatar/${player.tid_skin}?3d&size=45`
    },
  },
}
</script>
