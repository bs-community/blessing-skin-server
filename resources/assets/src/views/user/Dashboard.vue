<template>
  <div>
    <email-verification />
    <div class="box">
      <div class="box-header with-border">
        <h3 v-t="'user.used.title'" class="box-title" />
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="row">
          <div class="col-md-8">
            <div class="progress-group">
              <span v-t="'user.used.players'" class="progress-text" />
              <span class="progress-number"><b>{{ playersUsed }}</b> / {{ playersTotal }}</span>
              <div class="progress sm">
                <div class="progress-bar progress-bar-aqua" :style="{ width: playersPercentage + '%' }" />
              </div>
            </div><!-- /.progress-group -->
            <div class="progress-group">
              <span v-t="'user.used.storage'" class="progress-text" />
              <span id="user-storage" class="progress-number">
                <template v-if="storageUsed > 1024">
                  <b>{{ round(storageUsed / 1024) }}</b> / {{ round(storageTotal / 1024) }} MB
                </template>
                <template v-else>
                  <b>{{ storageUsed }}</b> / {{ storageTotal }} KB
                </template>
              </span>

              <div class="progress sm">
                <div
                  id="user-storage-bar"
                  class="progress-bar progress-bar-yellow"
                  :style="{ width: storagePercentage + '%' }"
                />
              </div>
            </div><!-- /.progress-group -->
          </div><!-- /.col -->
          <div class="col-md-4">
            <p class="text-center">
              <strong v-t="'user.cur-score'" />
            </p>
            <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
              {{ score }}
            </p>
            <p v-t="'user.score-notice'" class="text-center" style="font-size: smaller; margin-top: 20px;" />
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- ./box-body -->
      <div class="box-footer">
        <button v-if="canSign" class="btn btn-primary pull-left" @click="sign">
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;{{ $t('user.sign') }}
        </button>
        <button
          v-else
          class="btn btn-primary pull-left"
          :title="$t('user.last-sign', { time: lastSignAt.toLocaleString() })"
          disabled
        >
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;
          {{ remainingTimeText }}
        </button>
      </div><!-- /.box-footer -->
    </div>
  </div>
</template>

<script>
import EmailVerification from '../../components/EmailVerification.vue'

const ONE_DAY = 24 * 3600 * 1000

export default {
  name: 'Dashboard',
  components: {
    EmailVerification,
  },
  data: () => ({
    score: 0,
    lastSignAt: new Date(),
    signAfterZero: false,
    signGap: 0,
    playersUsed: 0,
    playersTotal: 0,
    storageUsed: 0,
    storageTotal: 0,
  }),
  computed: {
    playersPercentage() {
      return this.playersUsed / this.playersTotal * 100
    },
    storagePercentage() {
      return this.storageUsed / this.storageTotal * 100
    },
    signRemainingTime() {
      if (this.signAfterZero) {
        const today = (new Date()).setHours(0, 0, 0, 0)
        const tomorrow = today + ONE_DAY
        return this.lastSignAt.valueOf() < today ? 0 : tomorrow - Date.now()
      }
      return this.lastSignAt.valueOf() + this.signGap - Date.now()
    },
    remainingTimeText() {
      const time = this.signRemainingTime / 1000 / 60
      if (time < 60) {
        return this.$t(
          'user.sign-remain-time',
          { time: ~~time, unit: this.$t('user.time-unit-min') }
        )
      }
      return this.$t(
        'user.sign-remain-time',
        { time: ~~(time / 60), unit: this.$t('user.time-unit-hour') }
      )
    },
    canSign() {
      return this.signRemainingTime <= 0
    },
  },
  beforeMount() {
    this.fetchScoreInfo()
  },
  methods: {
    async fetchScoreInfo() {
      const data = await this.$http.get('/user/score-info')
      this.score = data.user.score
      this.lastSignAt = new Date(data.user.lastSignAt)
      this.signAfterZero = data.signAfterZero
      this.signGap = data.signGapTime * 3600 * 1000
      this.playersUsed = data.stats.players.used
      this.playersTotal = data.stats.players.total
      this.storageUsed = data.stats.storage.used
      this.storageTotal = data.stats.storage.total
    },
    round: Math.round,
    async sign() {
      const result = await this.$http.post('/user/sign')

      if (result.errno === 0) {
        this.$message.success(result.msg)
        this.score = result.score
        this.lastSignAt = new Date()
        this.storageUsed = result.storage.used
        this.storageTotal = result.storage.total
      } else {
        this.$message.warning(result.msg)
      }
    },
  },
}
</script>

<style lang="stylus">
#score
  font-family Minecraft
  font-size 50px
  text-align center
  margin-top 20px
  cursor help

.progress
  margin-top 4px
</style>
