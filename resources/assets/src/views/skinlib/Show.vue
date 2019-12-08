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
          <button
            v-if="anonymous"
            class="btn btn-outline-secondary"
            disabled
            :title="$t('skinlib.show.anonymous')"
          >
            {{ $t('skinlib.addToCloset') }}
          </button>
          <div v-else class="d-flex justify-content-between">
            <div>
              <button
                v-if="liked"
                class="btn btn-outline-success mr-2"
                data-toggle="modal"
                data-target="#modal-use-as"
                @click="fetchPlayersList"
              >
                {{ $t('skinlib.apply') }}
              </button>
              <button
                v-if="liked"
                class="btn btn-outline-primary mr-2"
                data-test="removeFromCloset"
                @click="removeFromCloset"
              >
                {{ $t('skinlib.removeFromCloset') }}
              </button>
              <button
                v-else
                class="btn btn-outline-primary mr-2"
                data-test="addToCloset"
                @click="addToCloset"
              >
                {{ $t('skinlib.addToCloset') }}
              </button>
              <button
                v-if="type !== 'cape'"
                class="btn btn-outline-info mr-2"
                data-test="setAsAvatar"
                @click="setAsAvatar"
              >
                {{ $t('user.setAsAvatar') }}
              </button>
              <button
                v-if="canBeDownloaded"
                class="btn btn-outline-info mr-2"
                data-test="download"
                @click="download"
              >
                {{ $t('skinlib.show.download') }}
              </button>
              <button
                class="btn btn-outline-info mr-2"
                data-test="report"
                @click="report"
              >
                {{ $t('skinlib.report.title') }}
              </button>
            </div>
            <div
              class="pt-2 likes"
              :class="[liked ? 'text-red' : 'text-gray']"
              :title="$t('skinlib.show.likes')"
            >
              <i class="fas fa-heart" />
              <span>{{ likes }}</span>
            </div>
          </div>
        </template>
      </previewer>
    </div>

    <div class="col-md-4">
      <div class="card card-primary">
        <div class="card-header">
          <h3 v-t="'skinlib.show.detail'" class="card-title" />
        </div>
        <div class="card-body">
          <table class="table">
            <tbody>
              <tr>
                <td v-t="'skinlib.show.name'" />
                <td>
                  {{ name|truncate }}
                  <small v-if="hasEditPermission">
                    <a v-t="'skinlib.show.edit'" href="#" @click="changeTextureName" />
                  </small>
                </td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.model'" />
                <td>
                  <span v-if="type === 'cape'">{{ $t('general.cape') }}</span>
                  <span v-else>{{ type }}</span>
                  <small v-if="hasEditPermission">
                    <a
                      href="#"
                      data-toggle="modal"
                      data-target="#modal-type"
                      @click="editingType = type"
                    >
                      {{ $t('skinlib.show.edit') }}
                    </a>
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
                <td v-if="uploaderNickName !== null">
                  <a
                    :href="`${baseUrl}/skinlib?filter=${type === 'cape' ? 'cape' : 'skin'}&uploader=${uploader}`"
                  >{{ uploaderNickName }}</a>
                </td>
                <td v-else><span v-t="'general.unexistent-user'" /></td>
              </tr>
              <tr>
                <td v-t="'skinlib.show.upload-at'" />
                <td>{{ uploadAt }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="hasEditPermission" class="card card-warning">
        <div class="card-header">
          <h3 v-t="'admin.operationsTitle'" class="card-title" />
        </div>
        <div class="card-body">
          <p v-t="'skinlib.show.manage-notice'" />
        </div>

        <div class="card-footer">
          <div class="container d-flex justify-content-between">
            <button class="btn btn-warning" @click="togglePrivacy">
              {{ $t(togglePrivacyText) }}
            </button>
            <button class="btn btn-danger" @click="deleteTexture">
              {{ $t('skinlib.show.delete-texture') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <apply-to-player-dialog
      ref="useAs"
      :allow-add="false"
      :skin="type !== 'cape' ? tid : 0"
      :cape="type === 'cape' ? tid : 0"
    />

    <modal
      id="modal-type"
      :title="$t(this.$t('skinlib.setNewTextureModel'))"
      center
      @confirm="changeModel"
    >
      <label class="mr-3">
        <input
          v-model="editingType"
          type="radio"
          name="type"
          value="steve"
        >
        Steve
      </label>
      <label class="mr-3">
        <input
          v-model="editingType"
          type="radio"
          name="type"
          value="alex"
        >
        Alex
      </label>
      <label>
        <input
          v-model="editingType"
          type="radio"
          name="type"
          value="cape"
        >
        {{ $t('general.cape') }}
      </label>
    </modal>
  </div>
</template>

<script>
import Modal from '../../components/Modal.vue'
import setAsAvatar from '../../components/mixins/setAsAvatar'
import addClosetItem from '../../components/mixins/addClosetItem'
import removeClosetItem from '../../components/mixins/removeClosetItem'
import emitMounted from '../../components/mixins/emitMounted'
import truncateText from '../../components/mixins/truncateText'
import ApplyToPlayerDialog from '../../components/ApplyToPlayerDialog.vue'
import { showModal, toast } from '../../scripts/notify'
import { truthy } from '../../scripts/validators'

export default {
  name: 'Show',
  components: {
    ApplyToPlayerDialog,
    Modal,
    Previewer: () => import('../../components/Previewer.vue'),
  },
  mixins: [
    emitMounted,
    addClosetItem,
    removeClosetItem,
    setAsAvatar,
    truncateText,
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
      editingType: 'steve',
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
        ({ value } = await showModal({
          mode: 'prompt',
          text: this.$t('skinlib.setNewTextureName'),
          input: this.name,
          validator: truthy(this.$t('skinlib.emptyNewTextureName')),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/rename',
        { tid: this.tid, new_name: value },
      )
      if (code === 0) {
        this.name = value
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
    async changeModel() {
      const { code, message } = await this.$http.post(
        '/skinlib/model',
        { tid: this.tid, model: this.editingType },
      )
      if (code === 0) {
        this.type = this.editingType
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
    async togglePrivacy() {
      try {
        await showModal({
          text: this.public
            ? this.$t('skinlib.setPrivateNotice')
            : this.$t('skinlib.setPublicNotice'),
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/privacy',
        { tid: this.tid },
      )
      if (code === 0) {
        toast.success(message)
        this.public = !this.public
      } else {
        toast.error(message)
      }
    },
    async deleteTexture() {
      try {
        await showModal({
          text: this.$t('skinlib.deleteNotice'),
          okButtonType: 'danger',
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/delete',
        { tid: this.tid },
      )
      if (code === 0) {
        toast.success(message)
        setTimeout(() => (window.location = `${this.baseUrl}/skinlib`), 1000)
      } else {
        toast.error(message)
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
        ({ value: reason } = await showModal({
          mode: 'prompt',
          title: this.$t('skinlib.report.title'),
          text: prompt,
          placeholder: this.$t('skinlib.report.reason'),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/skinlib/report',
        { tid: this.tid, reason },
      )
      if (code === 0) {
        toast.success(message)
      } else {
        toast.error(message)
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
