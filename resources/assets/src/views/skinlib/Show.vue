<template>
  <div class="row">
    <div class="col-md-8">
      <previewer
        :skin="type !== 'cape' && textureUrl"
        :cape="type === 'cape' ? textureUrl : ''"
        :init-position-z="60"
      >
        <template slot="footer">
          <button
            v-if="!auth"
            v-t="'skinlib.addToCloset'"
            disabled
            :title="$t('skinlib.show.anonymous')"
            class="btn btn-primary pull-right"
          />
          <template v-else>
            <a
              v-if="liked"
              v-t="'skinlib.apply'"
              :href="`${baseUrl}/user/closet?tid=${tid}`"
              class="btn btn-success pull-right pulled-right-btn"
            />
            <a
              v-if="liked"
              v-t="'skinlib.removeFromCloset'"
              class="btn btn-primary pull-right pulled-right-btn"
              @click="removeFromCloset"
            />
            <a
              v-else
              v-t="'skinlib.addToCloset'"
              class="btn btn-primary pull-right pulled-right-btn"
              @click="addToCloset"
            />
            <button
              v-if="type !== 'cape'"
              v-t="'user.setAsAvatar'"
              class="btn btn-default pull-right pulled-right-btn"
              @click="setAsAvatar"
            />
            <a
              v-if="canBeDownloaded"
              v-t="'skinlib.show.download'"
              class="btn btn-default pull-right"
              :href="`${baseUrl}/raw/${tid}.png`"
              :download="`${name}`.png"
            />
          </template>
          <div
            class="btn likes"
            style="cursor: auto;"
            :style="{ color: liked ? '#e0353b' : '#333' }"
            :title="$t('skinlib.show.likes')"
            data-toggle="tooltip"
            data-placement="top"
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
                  {{ name }}
                  <small v-if="uploader === currentUid || admin">
                    <a v-t="'skinlib.show.edit'" href="#" @click="changeTextureName" />
                  </small>
                </td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.model'" />
                <td>
                  <template v-if="type === 'cape'">{{ $t('general.cape') }}</template>
                  <template v-else>{{ type }}</template>
                  <small v-if="uploader === currentUid || admin">
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

      <div v-if="auth" class="box box-warning">
        <div class="box-header with-border">
          <h3 v-t="'admin.operationsTitle'" class="box-title" />
        </div><!-- /.box-header -->
        <div class="box-body">
          <p v-t="'skinlib.show.manage-notice'" />
        </div><!-- /.box-body -->

        <div class="box-footer">
          <a v-t="togglePrivacyText" class="btn btn-warning" @click="togglePrivacy" />
          <a v-t="'skinlib.show.delete-texture'" class="btn btn-danger pull-right" @click="deleteTexture" />
        </div><!-- /.box-footer -->
      </div>
    </div>
  </div>
</template>

<script>
import setAsAvatar from '../../components/mixins/setAsAvatar'
import addClosetItem from '../../components/mixins/addClosetItem'
import removeClosetItem from '../../components/mixins/removeClosetItem'

export default {
  name: 'Show',
  components: {
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
    }
  },
  computed: {
    auth() {
      return !!this.currentUid
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
      const data = await this.$http.get(`/skinlib/info/${this.tid}`)
      this.name = data.name
      this.type = data.type
      this.likes = data.likes
      this.hash = data.hash
      this.uploader = data.uploader
      this.size = data.size
      this.uploadAt = data.upload_at
      this.public = !!data.public
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

      const { errno, msg } = await this.$http.post(
        '/skinlib/rename',
        { tid: this.tid, new_name: value }
      )
      if (errno === 0) {
        this.name = value
        this.$message.success(msg)
      } else {
        this.$message.error(msg)
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

      const { errno, msg } = await this.$http.post(
        '/skinlib/model',
        { tid: this.tid, model: value }
      )
      if (errno === 0) {
        this.type = value
        this.$message.success(msg)
      } else {
        this.$message.warning(msg)
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

      const { errno, msg } = await this.$http.post(
        '/skinlib/privacy',
        { tid: this.tid }
      )
      if (errno === 0) {
        this.$message.success(msg)
        this.public = !this.public
      } else {
        this.$message.warning(msg)
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

      const { errno, msg } = await this.$http.post(
        '/skinlib/delete',
        { tid: this.tid }
      )
      if (errno === 0) {
        this.$message.success(msg)
        setTimeout(() => (window.location = `${this.baseUrl}/skinlib`), 1000)
      } else {
        this.$message.warning(msg)
      }
    },
  },
}
</script>

<style lang="stylus">
.table > tbody > tr > td
  border-top 0

.pulled-right-btn
  margin-left 12px
</style>
