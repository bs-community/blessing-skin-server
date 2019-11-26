<template>
  <form @submit.prevent="submit">
    <div v-if="players.length">
      <p v-t="'user.bindExistedPlayer'" />
      <div class="form-group mb-3">
        <select v-model="selected" class="form-control player-select">
          <option v-for="name in players" :key="name">{{ name }}</option>
        </select>
      </div>
    </div>

    <div v-else>
      <p v-t="'user.bindNewPlayer'" />
      <div class="form-group mb-3">
        <input
          v-model="selected"
          class="form-control"
          :placeholder="$t('general.player.player-name')"
        >
      </div>
    </div>

    <div v-show="message" class="alert alert-warning" v-text="message" />

    <button class="btn btn-primary float-right" type="submit" :disabled="pending">
      <template v-if="pending">
        <i class="fa fa-spinner fa-spin" /> {{ $t('general.wait') }}
      </template>
      <span v-else>{{ $t('general.submit') }}</span>
    </button>
  </form>
</template>

<script>
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'BindPlayer',
  mixins: [
    emitMounted,
  ],
  data() {
    return {
      players: [],
      selected: '',
      pending: false,
      message: '',
    }
  },
  mounted() {
    this.fetchPlayers()
  },
  methods: {
    async fetchPlayers() {
      const players = (await this.$http.get('/user/player/list')).data
      this.players = players.map(player => player.name)
      ;[this.selected] = this.players
    },
    async submit() {
      this.pending = true
      const { code, message } = await this.$http.post(
        '/user/player/bind',
        { player: this.selected },
      )
      this.pending = false
      if (code === 0) {
        await this.$alert(message)
        window.location.href = `${document.baseURI}user`
      } else {
        this.message = message
      }
    },
  },
}
</script>

<style lang="stylus">
@import "../../styles/auth.styl"

.player-select
  width 100%
</style>
