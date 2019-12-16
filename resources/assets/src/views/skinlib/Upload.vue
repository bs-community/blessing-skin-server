<template>
  <div>
    <portal selector="#file-input" :disabled="disablePortal">
      <div class="card card-primary">
        <div class="card-body">
          <div class="form-group">
            <label v-t="'skinlib.upload.texture-name'" for="name" />
            <input
              v-model="name"
              type="text"
              :placeholder="textureNameRule"
              class="form-control"
            >
          </div>

          <div class="form-group">
            <label v-t="'skinlib.upload.texture-type'" />
            <br>
            <label class="mr-2">
              <input
                v-model="type"
                type="radio"
                name="type"
                value="steve"
              >
              Steve
            </label>
            <label class="mr-2">
              <input
                v-model="type"
                type="radio"
                name="type"
                value="alex"
              >
              Alex
            </label>
            <label class="mr-2">
              <input
                v-model="type"
                type="radio"
                name="type"
                value="cape"
              >
              {{ $t('general.cape') }}
            </label>
          </div>

          <div class="form-group">
            <label v-t="'skinlib.upload.select-file'" for="file" />
            <div class="file-dnd">
              <img v-if="hasFile" :src="texture" :width="width2d">
              <h3 v-else v-t="'skinlib.upload.dropZone'" />
            </div>
            <file-upload
              ref="upload"
              v-model="files"
              extensions="png"
              accept="image/png,image/x-png"
              drop=".file-dnd"
              @input-file="inputFile"
            >
              <button class="btn btn-primary">
                {{ $t('skinlib.upload.select-file') }}
              </button>
            </file-upload>
            <button
              v-show="hasFile"
              class="btn btn-danger float-right"
              data-test="remove"
              @click="remove"
            >
              <i class="fas fa-trash-alt" />
              {{ $t('skinlib.upload.remove') }}
            </button>
          </div>

          <!-- eslint-disable-next-line vue/no-v-html -->
          <div v-if="contentPolicy" class="callout callout-warning" v-html="contentPolicy" />
        </div>

        <div class="card-footer">
          <div class="container pl-0 pr-0 d-flex justify-content-between">
            <label
              class="mt-2"
              :title="$t('skinlib.upload.privacy-notice')"
              data-toggle="tooltip"
            >
              <input v-model="isPrivate" type="checkbox">
              {{ $t('skinlib.upload.set-as-private') }}
            </label>
            <button v-if="uploading" class="btn btn-success" disabled>
              <i class="fa fa-spinner fa-spin" /> {{ $t('skinlib.uploading') }}
            </button>
            <button v-else class="btn btn-success" @click="upload">
              {{ $t('skinlib.upload.button') }}
            </button>
          </div>
          <div v-if="hasFile" class="callout callout-info bottom-notice">
            <p>{{ $t('skinlib.upload.cost', { score: scoreCost }) }}</p>
          </div>
          <div v-if="isPrivate" class="callout callout-info bottom-notice">
            <p>{{ privacyNotice }}</p>
          </div>
          <div v-if="!isPrivate && award" class="callout callout-success bottom-notice">
            <p>{{ $t('skinlib.upload.award', { score: award }) }}</p>
          </div>
        </div>
      </div>
    </portal>

    <portal selector="#previewer" :disabled="disablePortal">
      <previewer
        :skin="type !== 'cape' ? texture : ''"
        :cape="type === 'cape' ? texture : ''"
        :model="type"
      />
    </portal>
  </div>
</template>

<script>
import FileUpload from 'vue-upload-component'
import { isSlimSkin } from 'skinview3d'
import Portal from '../../components/Portal'
import emitMounted from '../../components/mixins/emitMounted'
import { toast } from '../../scripts/notify'

export default {
  name: 'Upload',
  components: {
    FileUpload,
    Portal,
    Previewer: () => import('../../components/Previewer.vue'),
  },
  mixins: [
    emitMounted,
  ],
  data() {
    return {
      name: '',
      type: 'steve',
      isPrivate: false,
      files: [],
      texture: '',
      uploading: false,
      textureNameRule: blessing.extra.rule,
      privacyNotice: blessing.extra.privacyNotice,
      scorePublic: blessing.extra.scorePublic,
      scorePrivate: blessing.extra.scorePrivate,
      award: blessing.extra.award,
      contentPolicy: blessing.extra.contentPolicy,
      width2d: 64,
      disablePortal: process.env.NODE_ENV === 'test',
    }
  },
  computed: {
    scoreCost() {
      const size = Math.round(this.files[0].size / 1024) || 1
      return size * (this.isPrivate ? this.scorePrivate : this.scorePublic)
    },
    hasFile() {
      return this.files[0]
    },
  },
  methods: {
    async upload() {
      if (!this.hasFile) {
        toast.error(this.$t('skinlib.emptyUploadFile'))
        return
      }

      if (!this.name) {
        toast.error(this.$t('skinlib.emptyTextureName'))
        return
      }

      if (!/image\/(x-)?png/.test(this.files[0].type)) {
        toast.error(this.$t('skinlib.fileExtError'))
        return
      }

      const data = new FormData()
      data.append('name', this.name)
      data.append('type', this.type)
      data.append('file', this.files[0].file, this.files[0].name)
      data.append('public', !this.isPrivate)

      this.uploading = true
      const {
        code, message, data: { tid } = { tid: 0 },
      } = await this.$http.post('/skinlib/upload', data)
      if (code === 0) {
        window.location = `${blessing.base_url}/skinlib/show/${tid}`
      } else {
        toast.error(message)
        this.uploading = false
      }
    },
    inputFile(file) {
      if (!file) {
        return
      }

      if (!this.name) {
        const matched = /(.*)\.png$/i.exec(file.name)
        this.name = matched ? matched[1] : file.name
      }
      this.texture = URL.createObjectURL(file.file)

      const image = new Image()
      image.src = this.texture
      image.onload = () => {
        this.width2d = image.width > 400 ? 400 : image.width
        this.type = isSlimSkin(image) ? 'alex' : 'steve'
      }
    },
    remove() {
      this.$refs.upload.clear()
      this.texture = ''
    },
  },
}
</script>

<style lang="stylus">
.file-dnd
  min-height 256px
  margin-bottom 15px
  border 1px solid #ddd
  border-radius 2px
  display flex
  justify-content center
  align-items center

  h3
    color #aaa

.bottom-notice
  margin-top 10px
</style>
