<template>
  <div class="row">
    <div class="col-md-8">
      <previewer
        :skin="type !== 'cape' && textureUrl"
        :cape="type === 'cape' ? textureUrl : ''"
        :model="type"
        :init-position-z="60"
      >
        <template #footer>
          <el-button v-if="anonymous" disabled :title="$t('skinlib.show.anonymous')">
            {{ $t('skinlib.addToCloset') }}
          </el-button>
          <template v-else>
            <el-button
              v-if="liked"
              type="success"
              size="medium"
              data-toggle="modal"
              data-target="#modal-use-as"
              @click="fetchPlayersList"
            >
              {{ $t('skinlib.apply') }}
            </el-button>
            <el-button
              v-if="liked"
              type="primary"
              size="medium"
              data-test="removeFromCloset"
              @click="removeFromCloset"
            >
              {{ $t('skinlib.removeFromCloset') }}
            </el-button>
            <el-button
              v-else
              type="primary"
              size="medium"
              data-test="addToCloset"
              @click="addToCloset"
            >
              {{ $t('skinlib.addToCloset') }}
            </el-button>
            <el-button
              v-if="type !== 'cape'"
              size="medium"
              data-test="setAsAvatar"
              @click="setAsAvatar"
            >
              {{ $t('user.setAsAvatar') }}
            </el-button>
            <el-button
              v-if="canBeDownloaded"
              size="medium"
              data-test="download"
              @click="download"
            >
              {{ $t('skinlib.show.download') }}
            </el-button>
            <el-button
              type="warning"
              size="medium"
              data-test="report"
              @click="report"
            >
              {{ $t('skinlib.report.title') }}
            </el-button>
          </template>
          <div
            class="btn likes pull-right"
            style="cursor: auto;"
            :style="{ color: liked ? '#e0353b' : '#333' }"
            :title="$t('skinlib.show.likes')"
          >
            <i class="fas fa-heart" />
            <span>{{ likes }}</span>
          </div>
        </template>
      </previewer>
    </div>

    <div class="col-md-4">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 v-t="'skinlib.show.detail'" class="box-title" />
        </div>
        <div class="box-body">
          <table class="table">
            <tbody>
              <tr>
                <td v-t="'skinlib.show.name'" />
                <td>
                  {{ name.length > 15 ? `${name.slice(0, 15)}...` : name }}
                  <small v-if="hasEditPermission">
                    <a v-t="'skinlib.show.edit'" href="#" @click="changeTextureName" />
                  </small>
                </td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.model'" />
                <td>
                  <template v-if="type === 'cape'">{{ $t('general.cape') }}</template>
                  <template v-else>{{ type }}</template>
                  <small v-if="hasEditPermission">
                    <a v-t="'skinlib.show.edit'" href="#" @click="changeModel" />
                  </small>
                </td>
              </tr>
              <tr>
                <td>Hash</td>
                <td>
                  <span :title="hash">{{ hash.slice(0, 15) }}...</span>
                </td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.size'" />
                <td>{{ size }} KB</td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.uploader'" />
                <template v-if="uploaderNickName !== null">
                  <td>
                    <a
                      :href="`${baseUrl}/skinlib?filter=${type === 'cape' ? 'cape' : 'skin'}&uploader=${uploader}`"
                    >{{ uploaderNickName }}</a>
                  </td>
                </template>
                <template v-else>
                  <td><span v-t="'general.unexistent-user'" /></td>
                </template>
              </tr>
              <tr>
                <td v-t="'skinlib.show.upload-at'" />
                <td>{{ uploadAt }}</td>
              </tr>
            </tbody>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->

      <div v-if="hasEditPermission" class="box box-warning">
        <div class="box-header with-border">
          <h3 v-t="'admin.operationsTitle'" class="box-title" />
        </div><!-- /.box-header -->
        <div class="box-body">
          <p v-t="'skinlib.show.manage-notice'" />
        </div><!-- /.box-body -->

        <div class="box-footer">
          <el-button type="warning" @click="togglePrivacy">{{ $t(togglePrivacyText) }}</el-button>
          <el-button type="danger" class="pull-right" @click="deleteTexture">
            {{ $t('skinlib.show.delete-texture') }}
          </el-button>
        </div><!-- /.box-footer -->
      </div>
    </div>

    <apply-to-player-dialog
      ref="useAs"
      :allow-add="false"
      :skin="type !== 'cape' ? tid : 0"
      :cape="type === 'cape' ? tid : 0"
    />
  </div>
</template>

<script>
import setAsAvatar from '../../components/mixins/setAsAvatar'
import addClosetItem from '../../components/mixins/addClosetItem'
import removeClosetItem from '../../components/mixins/removeClosetItem'
import ApplyToPlayerDialog from '../../components/ApplyToPlayerDialog.vue'

export default {
  name: 'Show',
  components: {
    ApplyToPlayerDialog,
    Previewer: () => import('../../components/Previewer.vue'),
  },
  mixins: [
    addClosetItem,
    removeClosetItem,
    setAsAvatar,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      tid: +this.$route[1],
      name: '',
      type: 'steve',
      likes: 0,
      hash: '',
      uploader: 0,
      size: 0,
      uploadAt: '',
      public: true,
      liked: blessing.extra.inCloset,
      canBeDownloaded: blessing.extra.download,
      currentUid: blessing.extra.currentUid,
      admin: blessing.extra.admin,
      uploaderNickName: blessing.extra.nickname,
      reportScore: blessing.extra.report,
    }
  },
  computed: {
    anonymous() {
      return !this.currentUid
    },
    hasEditPermission() {
      return this.uploader === this.currentUid || this.admin
    },
    togglePrivacyText() {
      return this.public ? 'skinlib.setAsPrivate' : 'skinlib.setAsPublic'
    },
    textureUrl() {
      return `${this.baseUrl}/textures/${this.hash}`
    },
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      const { data = {} } = await this.$http.get(`/skinlib/info/${this.tid}`)
      Object.assign(this.$data, data)
      this.uploadAt = data.upload_at
    },
    async addToCloset() {
      this.$once('like-toggled', () => {
        this.liked = true
        this.likes += 1
      })
      await this.addClosetItem()
    },
    async removeFromCloset() {
      this.$once('item-removed', () => {
        this.liked = false
        this.likes -= 1
      })
      await this.removeClosetItem()
    },
    download() {
      const a = document.createElement('a')
      a.href = `${this.baseUrl}/raw/${this.tid}.png`
      a.download = `${this.name}.png`
      const event = new MouseEvent('click')
      a.dispatchEvent(event)
    },
    async changeTextureName() {
      let value
      try {
        ({ value } = await this.$prompt(this.$t('skinlib.setNewTextureName'), {
          inputValue: this.name,
          inputValidator: name => !!name || this.$t('skinlib.emptyNewTextureName'),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/rename',
        { tid: this.tid, new_name: value }
      )
      if (code === 0) {
        this.name = value
        this.$message.success(message)
      } else {
        this.$message.error(message)
      }
    },
    async changeModel() {
      const h = this.$createElement
      const vnode = h('div', null, [
        h('span', null, this.$t('skinlib.setNewTextureModel')),
        h('select', { attrs: { selectedIndex: 0 } }, [
          h('option', { attrs: { value: 'steve' } }, 'Steve'),
          h('option', { attrs: { value: 'alex' } }, 'Alex'),
          h('option', { attrs: { value: 'cape' } }, this.$t('general.cape')),
        ]),
      ])
      try {
        await this.$msgbox({
          message: vnode,
          showCancelButton: true,
        })
      } catch {
        return
      }
      const value = ['steve', 'alex', 'cape'][vnode.children[1].elm.selectedIndex]

      const { code, message } = await this.$http.post(
        '/skinlib/model',
        { tid: this.tid, model: value }
      )
      if (code === 0) {
        this.type = value
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async togglePrivacy() {
      try {
        await this.$confirm(
          this.public
            ? this.$t('skinlib.setPrivateNotice')
            : this.$t('skinlib.setPublicNotice'),
          { type: 'warning' }
        )
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/privacy',
        { tid: this.tid }
      )
      if (code === 0) {
        this.$message.success(message)
        this.public = !this.public
      } else {
        this.$message.warning(message)
      }
    },
    async deleteTexture() {
      try {
        await this.$confirm(
          this.$t('skinlib.deleteNotice'),
          { type: 'warning' }
        )
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/delete',
        { tid: this.tid }
      )
      if (code === 0) {
        this.$message.success(message)
        setTimeout(() => (window.location = `${this.baseUrl}/skinlib`), 1000)
      } else {
        this.$message.warning(message)
      }
    },
    async report() {
      const prompt = (() => {
        if (this.reportScore > 0) {
          return this.$t('skinlib.report.positive', { score: this.reportScore })
        } else if (this.reportScore < 0) {
          return this.$t('skinlib.report.negative', { score: -this.reportScore })
        }
        return ''
      })()
      let reason
      try {
        ({ value: reason } = await this.$prompt(prompt, {
          title: this.$t('skinlib.report.title'),
          inputPlaceholder: this.$t('skinlib.report.reason'),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/report',
        { tid: this.tid, reason }
      )
      if (code === 0) {
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    fetchPlayersList() {
      this.$refs.useAs.fetchList()
    },
  },
}
</script>

<style lang="stylus">
.table > tbody > tr > td
  border-top 0
</style>
