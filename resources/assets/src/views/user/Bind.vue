<template>
  <form @submit.prevent="submit">
    <div v-if="players.length">
      <p v-t="'user.bindExistedPlayer'" />
      <div class="form-group">
        <select v-model="selected" class="player-select">
          <option v-for="name in players" :key="name">{{ name }}</option>
        </select>
      </div>
    </div>

    <div v-else>
      <p v-t="'user.bindNewPlayer'" />
      <div class="form-group has-feedback">
        <input
          v-model="selected"
          class="form-control"
          :placeholder="$t('general.player.player-name')"
        >
        <span class="glyphicon glyphicon-user form-control-feedback" />
      </div>
    </div>

    <div v-show="message" class="callout callout-warning" v-text="message" />


    <el-button type="primary" native-type="submit" :disabled="pending">
      <template v-if="pending">
        <i class="fa fa-spinner fa-spin" /> {{ $t('general.wait') }}
      </template>
      <span v-else>{{ $t('general.submit') }}</span>
    </el-button>
  </form>
</template>

<script>
export default {
  name: 'BindPlayer',
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
      const players = await this.$http.get('/user/player/list')
      this.players = players.map(player => player.name)
      ;[this.selected] = this.players
    },
    async submit() {
      this.pending = true
      const { errno, msg } = await this.$http.post(
        '/user/player/bind',
        { player: this.selected }
      )
      this.pending = false
      if (errno === 0) {
        await this.$alert({ message: msg, type: 'success' })
        window.location.href = `${blessing.base_url}/user`
      } else {
        this.message = msg
      }
    },
  },
}
</script>

<style lang="stylus">
@import "../../stylus/auth.styl"

.player-select
  width 100%
</style>
