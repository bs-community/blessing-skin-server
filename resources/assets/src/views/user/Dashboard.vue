<template>
  <div>
    <email-verification />
    <div class="box">
      <div class="box-header with-border">
        <h3 v-t="'user.used.title'" class="box-title" />
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="row">
          <div class="col-md-1" />
          <div class="col-md-6">
            <table class="usage-table" border="0">
              <tbody>
                <tr>
                  <td class="text-center">
                    <b v-t="'user.used.players'" />
                  </td>
                  <td class="circle-gap" />
                  <td class="text-center">
                    <b v-t="'user.used.storage'" />
                  </td>
                </tr>
                <tr class="line-gap" />
                <tr>
                  <td>
                    <el-progress
                      type="circle"
                      :percentage="~~playersPercentage"
                      color="#00429B"
                    />
                  </td>
                  <td class="circle-gap" />
                  <td>
                    <el-progress
                      type="circle"
                      :percentage="~~storagePercentage"
                      color="#008C95"
                    />
                  </td>
                </tr>
                <tr class="line-gap" />
                <tr>
                  <td class="text-center">
                    <b>{{ playersUsed }}</b> / {{ playersTotal }}
                  </td>
                  <td class="circle-gap" />
                  <td class="text-center">
                    <template v-if="storageUsed > 1024">
                      <b>{{ ~~(storageUsed / 1024) }}</b> / {{ ~~(storageTotal / 1024) }} MB
                    </template>
                    <template v-else>
                      <b>{{ storageUsed }}</b> / {{ storageTotal }} KB
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-md-4">
            <p class="text-center score-title">
              <strong v-t="'user.cur-score'" />
            </p>
            <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
              {{ animatedScore }}
            </p>
            <p v-t="'user.score-notice'" class="text-center score-notice" />
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- ./box-body -->
      <div class="box-footer">
        <el-button
          v-if="canSign"
          class="btn btn-primary pull-left"
          type="primary"
          round
          @click="sign"
        >
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;{{ $t('user.sign') }}
        </el-button>
        <el-button
          v-else
          class="btn btn-primary pull-left"
          type="primary"
          round
          :title="$t('user.last-sign', { time: lastSignAt.toLocaleString() })"
          disabled
        >
          <i class="far fa-calendar-check" aria-hidden="true" /> &nbsp;
          {{ remainingTimeText }}
        </el-button>
      </div><!-- /.box-footer -->
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import 'element-ui/lib/theme-chalk/progress.css'
import Progress from 'element-ui/lib/progress'
import Tween from '@tweenjs/tween.js'
import EmailVerification from '../../components/EmailVerification.vue'

Vue.use(Progress)

const ONE_DAY = 24 * 3600 * 1000

export default {
  name: 'Dashboard',
  components: {
    EmailVerification,
  },
  data: () => ({
    score: 0,
    tweenedScore: 0,
    lastSignAt: new Date(),
    signAfterZero: false,
    signGap: 0,
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
      const {
        code, message, data,
      } = await this.$http.post('/user/sign')

      if (code === 0) {
        this.$message.success(message)
        this.score = data.score
        this.lastSignAt = new Date()
        this.storageUsed = data.storage.used
        this.storageTotal = data.storage.total
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.usage-table
  margin 0 auto

.circle-gap
  width 14%

.line-gap
  height 12px

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
