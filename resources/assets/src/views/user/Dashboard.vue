<template>
  <div>
    <email-verification />
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 v-t="'user.used.title'" class="card-title" />
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-1" />
          <div class="col-md-6">
            <div class="info-box bg-teal">
              <span class="info-box-icon">
                <i class="fas fa-gamepad" />
              </span>
              <div class="info-box-content">
                <span class="info-box-text">{{ $t('user.used.players') }}</span>
                <span class="info-box-number">
                  <b>{{ playersUsed }}</b> / {{ playersTotal }}
                </span>
                <div class="progress">
                  <div class="progress-bar" :style="{ width: playersPercentage + '%' }" />
                </div>
              </div>
            </div>
            <div class="info-box bg-maroon">
              <span class="info-box-icon">
                <i class="fas fa-hdd" />
              </span>
              <div class="info-box-content">
                <span class="info-box-text">{{ $t('user.used.storage') }}</span>
                <span class="info-box-number">
                  <template v-if="storageUsed > 1024">
                    <b>{{ ~~(storageUsed / 1024) }}</b> / {{ ~~(storageTotal / 1024) }} MB
                  </template>
                  <template v-else>
                    <b>{{ storageUsed }}</b> / {{ storageTotal }} KB
                  </template>
                </span>
                <div class="progress">
                  <div class="progress-bar" :style="{ width: storagePercentage + '%' }" />
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <p class="text-center score-title">
              <strong v-t="'user.cur-score'" />
            </p>
            <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
              {{ animatedScore }}
            </p>
            <p v-t="'user.score-notice'" class="text-center score-notice" />
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button
          v-if="canSign"
          class="btn bg-gradient-primary pl-5 pr-5"
          :disabled="signing"
          @click="sign"
        >
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;{{ $t('user.sign') }}
        </button>
        <button
          v-else
          class="btn bg-gradient-primary pl-4 pr-4"
          :title="$t('user.last-sign', { time: lastSignAt.toLocaleString() })"
          disabled
        >
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;
          {{ remainingTimeText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Tween from '@tweenjs/tween.js'
import EmailVerification from '../../components/EmailVerification.vue'
import emitMounted from '../../components/mixins/emitMounted'
import { toast } from '../../scripts/notify'

const ONE_DAY = 24 * 3600 * 1000

export default {
  name: 'Dashboard',
  components: {
    EmailVerification,
  },
  mixins: [
    emitMounted,
  ],
  data: () => ({
    score: 0,
    tweenedScore: 0,
    lastSignAt: new Date(),
    signAfterZero: false,
    signGap: 0,
    signing: false,
    playersUsed: 0,
    playersTotal: 1,
    storageUsed: 0,
    storageTotal: 1,
    tween: null,
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
        const today = new Date().setHours(0, 0, 0, 0)
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
          { time: ~~time, unit: this.$t('user.time-unit-min') },
        )
      }
      return this.$t(
        'user.sign-remain-time',
        { time: ~~(time / 60), unit: this.$t('user.time-unit-hour') },
      )
    },
    canSign() {
      return this.signRemainingTime <= 0
    },
    animatedScore() {
      return this.tweenedScore.toFixed(0)
    },
  },
  watch: {
    score(newValue) {
      this.tween.to({ tweenedScore: newValue }, 1000).start()
    },
  },
  created() {
    this.tween = new Tween.Tween(this.$data)
  },
  beforeMount() {
    this.fetchScoreInfo()
  },
  mounted() {
    function animate() {
      requestAnimationFrame(animate)
      Tween.update()
    }
    animate()
  },
  methods: {
    async fetchScoreInfo() {
      const { data } = await this.$http.get('/user/score-info')
      this.lastSignAt = new Date(data.user.lastSignAt)
      this.signAfterZero = data.signAfterZero
      this.signGap = data.signGapTime * 3600 * 1000
      this.playersUsed = data.stats.players.used
      this.playersTotal = data.stats.players.total
      this.storageUsed = data.stats.storage.used
      this.storageTotal = data.stats.storage.total
      this.score = data.user.score
    },
    async sign() {
      this.signing = true
      const {
        code, message, data,
      } = await this.$http.post('/user/sign')

      if (code === 0) {
        toast.success(message)
        this.score = data.score
        this.lastSignAt = new Date()
        this.storageUsed = data.storage.used
        this.storageTotal = data.storage.total
      } else {
        toast.warning(message)
      }
      this.signing = false
    },
  },
}
</script>

<style lang="stylus">
.score-title
  margin-top 5px

  @media (max-width 768px)
    margin-top 12px

#score
  font-family Minecraft
  font-size 50px
  text-align center
  margin-top 20px
  cursor help

.score-notice
  font-size smaller
  margin-top 20px
</style>
