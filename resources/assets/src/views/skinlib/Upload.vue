<template>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-body">
            <div class="form-group">
              <label v-t="'skinlib.upload.texture-name'" for="name" />
              <input
                v-model="name"
                class="form-control"
                type="text"
                :placeholder="textureNameRule"
              >
            </div>

            <div class="form-group">
              <label v-t="'skinlib.upload.texture-type'" />
              <br>
              <label>
                <input
                  v-model="type"
                  type="radio"
                  name="type"
                  value="steve"
                  checked
                > Steve
              </label>&nbsp;
              <label>
                <input
                  v-model="type"
                  type="radio"
                  name="type"
                  value="alex"
                > Alex
              </label>&nbsp;
              <label>
                <input
                  v-model="type"
                  type="radio"
                  name="type"
                  value="cape"
                > {{ $t('general.cape') }}
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
                <span class="btn btn-primary">
                  {{ $t('skinlib.upload.select-file') }}
                </span>
              </file-upload>
              <button
                v-show="hasFile"
                class="btn btn-default pull-right"
                @click="remove"
              >
                <i class="fas fa-trash-alt" />
                {{ $t('skinlib.upload.remove') }}
              </button>
            </div>

            <div v-if="isPrivate" class="callout callout-info">
              <p>{{ privacyNotice }}</p>
            </div>
            <div v-if="!isPrivate && award" class="callout callout-success">
              <p>{{ $t('skinlib.upload.award', { score: award }) }}</p>
            </div>
          </div><!-- /.box-body -->

          <div class="box-footer">
            <label
              for="private"
              class="pull-right"
              :title="$t('skinlib.upload.privacy-notice')"
              data-placement="top"
              data-toggle="tooltip"
            >
              <input v-model="isPrivate" type="checkbox"> {{ $t('skinlib.upload.set-as-private') }}
            </label>
            <button v-if="uploading" class="btn btn-primary" disabled>
              <i class="fa fa-spinner fa-spin" /> {{ $t('skinlib.uploading') }}
            </button>
            <button v-else class="btn btn-primary" @click="upload">
              {{ $t('skinlib.upload.button') }}
            </button>
            &nbsp; {{ hasFile && $t('skinlib.upload.cost', { score: scoreCost }) }}
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
import FileUpload from 'vue-upload-component'
import toastr from 'toastr'
import { swal } from '../../js/notify'

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
      award: +blessing.extra.award,
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
        toastr.info(this.$t('skinlib.emptyUploadFile'))
        return
      }

      if (!this.name) {
        toastr.info(this.$t('skinlib.emptyTextureName'))
        return
      }

      if (!/image\/(x-)?png/.test(this.files[0].type)) {
        toastr.info(this.$t('skinlib.fileExtError'))
        return
      }

      const data = new FormData()
      data.append('name', this.name)
      data.append('type', this.type)
      data.append('file', this.files[0].file, this.files[0].name)
      data.append('public', !this.isPrivate)

      this.uploading = true
      const {
        errno, msg, tid,
      } = await this.$http.post('/skinlib/upload', data)
      if (errno === 0) {
        await swal({ type: 'success', text: msg })
        toastr.info(this.$t('skinlib.redirecting'))
        setTimeout(() => {
          window.location = `${blessing.base_url}/skinlib/show/${tid}`
        }, 1000)
      } else {
        await swal({ type: 'warning', text: msg })
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
</style>
