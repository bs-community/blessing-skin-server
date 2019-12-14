<template>
  <div>
    <portal selector="#email-verification"><email-verification /></portal>

    <portal selector="#closet-list">
      <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1 pl-1">
          <div class="d-flex justify-content-between">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: category === 'skin' }"
                  href="#"
                  data-toggle="pill"
                  role="tab"
                  @click="switchCategory"
                >
                  {{ $t('general.skin') }}
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: category === 'cape' }"
                  href="#"
                  @click="switchCategory"
                >
                  {{ $t('general.cape') }}
                </a>
              </li>
              <li class="nav-item d-none d-md-block">
                <a
                  v-t="'user.closet.upload'"
                  :href="`${baseUrl}/skinlib/upload`"
                  class="nav-link"
                />
              </li>
            </ul>
            <div class="mr-3 my-2 my-lg-0">
              <input
                v-model="query"
                class="form-control mr-sm-2"
                type="search"
                aria-label="Search"
                :placeholder="$t('user.typeToSearch')"
                @input="search"
              >
            </div>
          </div>
        </div>
        <div class="card-body">
          <div
            v-if="category === 'skin'"
            id="skin-category"
            :class="{ active: category === 'skin' }"
          >
            <div v-if="skinItems.length === 0" class="text-center p-3">
              <div v-if="query !== ''" v-t="'general.noResult'" />
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div v-else v-html="$t('user.emptyClosetMsg', { url: linkToSkin })" />
            </div>
            <div v-else class="d-flex flex-wrap">
              <closet-item
                v-for="(item, index) in skinItems"
                :key="item.tid"
                :tid="item.tid"
                :name="item.name"
                :type="item.type"
                :selected="selectedSkin === item.tid"
                @select="selectTexture(item.tid)"
                @item-removed="removeSkinItem(index)"
              />
            </div>
          </div>
          <div
            v-else
            id="cape-category"
            :class="{ active: category === 'cape' }"
          >
            <div v-if="capeItems.length === 0" class="text-center p-3">
              <div v-if="query !== ''" v-t="'general.noResult'" />
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div v-else v-html="$t('user.emptyClosetMsg', { url: linkToCape })" />
            </div>
            <div v-else>
              <closet-item
                v-for="(item, index) in capeItems"
                :key="item.tid"
                :tid="item.tid"
                :name="item.name"
                :type="item.type"
                :selected="selectedCape === item.tid"
                @select="selectTexture(item.tid)"
                @item-removed="removeCapeItem(index)"
              />
            </div>
          </div>
        </div>
        <div class="card-footer">
          <paginate
            v-if="category === 'skin'"
            v-model="skinCurrentPage"
            :page-count="skinTotalPages"
            class="float-right"
            container-class="pagination pagination-sm no-margin"
            page-class="page-item"
            page-link-class="page-link"
            prev-class="page-item"
            prev-link-class="page-link"
            next-class="page-item"
            next-link-class="page-link"
            first-button-text="«"
            prev-text="‹"
            next-text="›"
            last-button-text="»"
            :click-handler="pageChanged"
            :first-last-button="true"
          />
          <paginate
            v-else
            v-model="capeCurrentPages"
            :page-count="capeTotalPages"
            class="float-right"
            container-class="pagination pagination-sm no-margin"
            page-class="page-item"
            page-link-class="page-link"
            prev-class="page-item"
            prev-link-class="page-link"
            next-class="page-item"
            next-link-class="page-link"
            first-button-text="«"
            prev-text="‹"
            next-text="›"
            last-button-text="»"
            :click-handler="pageChanged"
            :first-last-button="true"
          />
        </div>
      </div>
    </portal>

    <portal selector="#previewer">
      <previewer
        closet-mode
        :skin="skinUrl"
        :cape="capeUrl"
        :model="model"
      >
        <template #footer>
          <div class="d-flex justify-content-between">
            <button
              class="btn btn-primary"
              data-toggle="modal"
              data-target="#modal-use-as"
              @click="fetchPlayersList"
            >
              {{ $t('user.useAs') }}
            </button>
            <button class="btn btn-default" data-test="resetSelected" @click="resetSelected">
              {{ $t('user.resetSelected') }}
            </button>
          </div>
        </template>
      </previewer>
    </portal>

    <portal selector="#modals">
      <apply-to-player-dialog ref="useAs" :skin="selectedSkin" :cape="selectedCape" />
      <add-player-dialog @add="fetchPlayersList" />
    </portal>
  </div>
</template>

<script>
import Paginate from 'vuejs-paginate'
import { debounce, queryString } from '../../scripts/utils'
import Portal from '../../components/Portal'
import ClosetItem from '../../components/ClosetItem.vue'
import EmailVerification from '../../components/EmailVerification.vue'
import AddPlayerDialog from '../../components/AddPlayerDialog.vue'
import ApplyToPlayerDialog from '../../components/ApplyToPlayerDialog.vue'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'Closet',
  components: {
    Portal,
    Paginate,
    ClosetItem,
    Previewer: () => import('../../components/Previewer.vue'),
    EmailVerification,
    AddPlayerDialog,
    ApplyToPlayerDialog,
  },
  mixins: [
    emitMounted,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data: () => ({
    category: 'skin',
    query: '',
    skinItems: [],
    skinCurrentPage: 1,
    skinTotalPages: 1,
    capeItems: [],
    capeCurrentPages: 1,
    capeTotalPages: 1,
    selectedSkin: 0,
    skinUrl: '',
    model: 'steve',
    selectedCape: 0,
    capeUrl: '',
    linkToSkin: `${blessing.base_url}/skinlib?filter=skin`,
    linkToCape: `${blessing.base_url}/skinlib?filter=cape`,
  }),
  created() {
    this.search = debounce(this.loadCloset, 350)
  },
  beforeMount() {
    this.loadCloset()
  },
  mounted() {
    const tid = +queryString('tid', 0)
    if (tid) {
      this.selectTexture(tid)
      this.fetchPlayersList()
      $('#modal-use-as').modal()
    }
  },
  methods: {
    /* istanbul ignore next */
    search() {}, // eslint-disable-line no-empty-function
    async loadCloset(page = 1) {
      const {
        data: {
          items, category, total_pages: totalPages,
        },
      } = await this.$http.get(
        '/user/closet-data',
        {
          category: this.category,
          q: this.query,
          page,
        },
      )
      this[`${category}TotalPages`] = totalPages
      this[`${category}Items`] = items
      this[`${category}CurrentPages`] = page
    },
    removeSkinItem(index) {
      this.$delete(this.skinItems, index)
    },
    removeCapeItem(index) {
      this.$delete(this.capeItems, index)
    },
    switchCategory() {
      this.category = this.category === 'skin' ? 'cape' : 'skin'
      this.loadCloset(this[`${this.category}CurrentPages`])
    },
    pageChanged(page) {
      this.loadCloset(page)
    },
    async selectTexture(tid) {
      const { data: { type, hash } } = await this.$http.get(`/skinlib/info/${tid}`)
      if (type === 'cape') {
        this.capeUrl = `/textures/${hash}`
        this.selectedCape = tid
      } else {
        this.skinUrl = `/textures/${hash}`
        this.selectedSkin = tid
        this.model = type
      }
    },
    resetSelected() {
      this.selectedSkin = 0
      this.selectedCape = 0
      this.skinUrl = ''
      this.capeUrl = ''
    },
    fetchPlayersList() {
      this.$refs.useAs.fetchList()
    },
  },
}
</script>
