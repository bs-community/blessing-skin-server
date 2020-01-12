<template>
  <modal
    id="modal-use-as"
    ref="modal"
    :title="$t('user.closet.use-as.title')"
    :ok-button-text="$t('general.submit')"
    flex-footer
    center
  >
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
    <template #footer>
      <a
        v-if="allowAdd"
        v-t="'user.closet.use-as.add'"
        data-toggle="modal"
        data-target="#modal-add-player"
        class="btn btn-default"
        href="#"
      />
      <button class="btn btn-primary" data-test="submit" @click="submit">
        {{ $t('general.submit') }}
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
      selected: 0,
    }
  },
  methods: {
    async fetchList() {
      this.players = (await this.$http.get('/user/player/list')).data
    },
    async submit() {
      if (!this.selected) {
        toast.info(this.$t('user.emptySelectedPlayer'))
        return
      }

      if (!this.skin && !this.cape) {
        toast.info(this.$t('user.emptySelectedTexture'))
        return
      }

      const { code, message } = await this.$http.post(
        `/user/player/set/${this.selected}`,
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
      return `${blessing.base_url}/avatar/${player.tid_skin}?size=35`
    },
  },
}
</script>

<style lang="stylus">
.player-item:not(:nth-child(1))
  margin-top 10px
</style>
