<template>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-body">
            <div class="form-group">
              <label v-t="'skinlib.upload.texture-name'" for="name" />
              <el-input v-model="name" :placeholder="textureNameRule" clearable />
            </div>

            <div class="form-group">
              <label v-t="'skinlib.upload.texture-type'" />
              <br>
              <el-radio v-model="type" label="steve">Steve</el-radio>
              <el-radio v-model="type" label="alex">Alex</el-radio>
              <el-radio v-model="type" label="cape">{{ $t('general.cape') }}</el-radio>
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
                <el-button type="primary" size="medium">
                  {{ $t('skinlib.upload.select-file') }}
                </el-button>
              </file-upload>
              <el-button
                v-show="hasFile"
                size="medium"
                class="pull-right"
                data-test="remove"
                @click="remove"
              >
                <i class="fas fa-trash-alt" />
                {{ $t('skinlib.upload.remove') }}
              </el-button>
            </div>

            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-if="contentPolicy" class="callout callout-warning" v-html="contentPolicy" />
          </div>

          <div class="box-footer">
            <el-switch
              v-model="isPrivate"
              :active-text="$t('skinlib.upload.set-as-private')"
              class="pull-right"
              :title="$t('skinlib.upload.privacy-notice')"
            />
            <el-button
              v-if="uploading"
              type="success"
              size="medium"
              disabled
            >
              <i class="fa fa-spinner fa-spin" /> {{ $t('skinlib.uploading') }}
            </el-button>
            <el-button
              v-else
              type="success"
              size="medium"
              @click="upload"
            >
              {{ $t('skinlib.upload.button') }}
            </el-button>
            &nbsp; {{ hasFile && $t('skinlib.upload.cost', { score: scoreCost }) }}
            <div v-if="isPrivate" class="callout callout-info bottom-notice">
              <p>{{ privacyNotice }}</p>
            </div>
            <div v-if="!isPrivate && award" class="callout callout-success bottom-notice">
              <p>{{ $t('skinlib.upload.award', { score: award }) }}</p>
            </div>
          </div>
        </div><!-- /.box -->
      </div>
      <div class="col-md-6">
        <previewer
          :skin="type !== 'cape' && texture"
          :cape="type === 'cape' ? texture : ''"
        />
      </div>
    </div>
  </section>
</template>

<script>
import Vue from 'vue'
import FileUpload from 'vue-upload-component'
import 'element-ui/lib/theme-chalk/radio.css'
import Radio from 'element-ui/lib/radio'

Vue.use(Radio)

export default {
  name: 'Upload',
  components: {
    Previewer: () => import('../../components/Previewer.vue'),
    FileUpload,
  },
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
        this.$message.error(this.$t('skinlib.emptyUploadFile'))
        return
      }

      if (!this.name) {
        this.$message.error(this.$t('skinlib.emptyTextureName'))
        return
      }

      if (!/image\/(x-)?png/.test(this.files[0].type)) {
        this.$message.error(this.$t('skinlib.fileExtError'))
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
        this.$message.success(message)
        setTimeout(() => {
          window.location = `${blessing.base_url}/skinlib/show/${tid}`
        }, 1000)
      } else {
        this.$message.error(message)
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
      image.onload = () => (this.width2d = image.width > 400 ? 400 : image.width)
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
