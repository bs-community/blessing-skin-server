<template>
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between">
        <h3 class="card-title">
          <span v-html="$t(title)" /> <!-- eslint-disable-line vue/no-v-html -->
          <span class="badge bg-olive">{{ indicator }}</span>
        </h3>
        <div class="operations">
          <i
            data-toggle="tooltip"
            data-placement="bottom"
            :title="$t('general.walk') + ' / ' + $t('general.run')"
            class="fas fa-forward"
            @click="toggleRun"
          />
          <i
            data-toggle="tooltip"
            data-placement="bottom"
            :title="$t('general.rotation')"
            class="fas fa-redo-alt"
            @click="toggleRotate"
          />
          <i
            data-toggle="tooltip"
            data-placement="bottom"
            :title="$t('general.pause')"
            class="fas"
            :class="{ 'fa-pause': !paused, 'fa-play': paused }"
            @click="togglePause"
          />
          <i
            data-toggle="tooltip"
            data-placement="bottom"
            :title="$t('general.reset')"
            class="fas fa-stop"
            @click="reset"
          />
        </div>
      </div>
    </div>
    <div class="card-body">
      <div ref="previewer" class="previewer-3d">
        <!-- Container for 3D Preview -->
      </div>
    </div>
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script>
import * as skinview3d from 'skinview3d'
import { emit } from '../scripts/event'
import SkinSteve from '../../../misc/textures/steve.png'

export default {
  name: 'Previewer',
  props: {
    skin: String,
    cape: String,
    model: {
      type: String,
      default: 'steve',
    },
    closetMode: Boolean,
    title: {
      type: String,
      default: 'general.texturePreview',
    },
    initPositionZ: {
      type: Number,
      default: 70,
    },
  },
  data: () => ({
    paused: false,
  }),
  computed: {
    indicator() {
      if (!this.closetMode) {
        return ''
      }

      if (this.skin && this.cape) {
        return `${this.$t('general.skin')} & ${this.$t('general.cape')}`
      } else if (this.skin) {
        return this.$t('general.skin')
      } else if (this.cape) {
        return this.$t('general.cape')
      }
      return ''
    },
  },
  watch: {
    skin(url) {
      this.viewer.skinUrl = url || SkinSteve
    },
    cape(url) {
      if (!url) {
        this.viewer.playerObject.cape.visible = false
        return
      }
      this.viewer.capeUrl = url
    },
    model(value) {
      this.viewer.playerObject.skin.slim = value === 'alex'
    },
  },
  mounted() {
    this.initPreviewer()
    emit('skinViewerMounted', this.$refs.previewer)
  },
  beforeDestroy() {
    this.viewer.dispose()
  },
  methods: {
    initPreviewer() {
      this.viewer = new skinview3d.SkinViewer({
        domElement: this.$refs.previewer,
        width: this.$refs.previewer.clientWidth,
        height: this.$refs.previewer.clientHeight,
        detectModel: false,
        skinUrl: this.skin || SkinSteve,
        capeUrl: this.cape,
      })
      this.viewer.camera.position.z = this.initPositionZ
      this.viewer.animation = new skinview3d.CompositeAnimation()

      this.handles = {
        walk: this.viewer.animation.add(skinview3d.WalkingAnimation),
        run: this.viewer.animation.add(skinview3d.RunningAnimation),
        rotate: this.viewer.animation.add(skinview3d.RotatingAnimation),
      }
      this.handles.run.paused = true
      this.control = skinview3d.createOrbitControls(this.viewer)
    },
    togglePause() {
      this.paused = !this.paused
      this.viewer.animationPaused = !this.viewer.animationPaused
    },
    toggleRun() {
      this.handles.run.paused = !this.handles.run.paused
      this.handles.walk.paused = false
    },
    toggleRotate() {
      this.handles.rotate.paused = !this.handles.rotate.paused
    },
    reset() {
      this.viewer.dispose()
      this.initPreviewer()
      this.handles.walk.paused = true
      this.handles.run.paused = true
      this.handles.rotate.paused = true
      this.viewer.camera.position.z = 70
      this.viewer.playerObject.skin.slim = this.model === 'alex'
    },
  },
}
</script>

<style lang="stylus">
@media (min-width: 992px)
  .previewer-3d
    min-height 500px

.previewer-3d canvas
  cursor move

.operations
  i
    padding .5em .5em
    display inline
    &:hover
      color #555
      cursor pointer
</style>
