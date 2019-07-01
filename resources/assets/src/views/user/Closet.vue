<template>
  <section class="content">
    <email-verification />
    <div class="row">
      <!-- Left col -->
      <div class="col-md-8">
        <!-- Custom tabs -->
        <div class="nav-tabs-custom">
          <!-- Tabs within a box -->
          <ul class="nav nav-tabs">
            <li :class="{ active: category === 'skin' }">
              <a
                v-t="'general.skin'"
                href="#"
                class="category-switch"
                data-toggle="tab"
                @click="switchCategory"
              />
            </li>
            <li :class="{ active: category === 'cape' }">
              <a
                v-t="'general.cape'"
                href="#"
                class="category-switch"
                data-toggle="tab"
                @click="switchCategory"
              />
            </li>
            <li>
              <a
                v-t="'user.closet.upload'"
                :href="`${baseUrl}/skinlib/upload`"
                class="category-switch"
              />
            </li>

            <li class="pull-right" style="padding: 7px;">
              <div class="has-feedback pull-right">
                <div class="user-search-form">
                  <input
                    v-model="query"
                    type="text"
                    class="form-control input-sm"
                    :placeholder="$t('user.typeToSearch')"
                    @input="search"
                  >
                  <span class="glyphicon glyphicon-search form-control-feedback" />
                </div>
              </div>
            </li>
          </ul>
          <div class="tab-content no-padding">
            <div
              v-if="category === 'skin'"
              id="skin-category"
              class="tab-pane box-body"
              :class="{ active: category === 'skin' }"
            >
              <div v-if="skinItems.length === 0" class="empty-msg">
                <div v-if="query !== ''" v-t="'general.noResult'" />
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div v-else v-html="$t('user.emptyClosetMsg', { url: linkToSkin })" />
              </div>
              <div v-else>
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
              class="tab-pane box-body"
              :class="{ active: category === 'cape' }"
            >
              <div v-if="capeItems.length === 0" class="empty-msg">
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
          <div class="box-footer">
            <paginate
              v-if="category === 'skin'"
              v-model="skinCurrentPage"
              :page-count="skinTotalPages"
              class="pull-right"
              container-class="pagination pagination-sm no-margin"
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
              class="pull-right"
              container-class="pagination pagination-sm no-margin"
              first-button-text="«"
              prev-text="‹"
              next-text="›"
              last-button-text="»"
              :click-handler="pageChanged"
              :first-last-button="true"
            />
          </div>
        </div><!-- /.nav-tabs-custom -->
      </div>

      <!-- Right col -->
      <div class="col-md-4">
        <previewer
          closet-mode
          :skin="skinUrl"
          :cape="capeUrl"
          :model="model"
        >
          <template #footer>
            <el-button
              type="primary"
              data-toggle="modal"
              data-target="#modal-use-as"
              @click="fetchPlayersList"
            >
              {{ $t('user.useAs') }}
            </el-button>
            <el-button data-test="resetSelected" class="pull-right" @click="resetSelected">
              {{ $t('user.resetSelected') }}
            </el-button>
          </template>
        </previewer>
      </div>
    </div>
    <apply-to-player-dialog ref="useAs" :skin="selectedSkin" :cape="selectedCape" />
    <add-player-dialog @add="fetchPlayersList" />
  </section><!-- /.content -->
</template>

<script>
import Paginate from 'vuejs-paginate'
import { debounce, queryString } from '../../scripts/utils'
import ClosetItem from '../../components/ClosetItem.vue'
import EmailVerification from '../../components/EmailVerification.vue'
import AddPlayerDialog from '../../components/AddPlayerDialog.vue'
import ApplyToPlayerDialog from '../../components/ApplyToPlayerDialog.vue'

export default {
  name: 'Closet',
  components: {
    Paginate,
    ClosetItem,
    Previewer: () => import('../../components/Previewer.vue'),
    EmailVerification,
    AddPlayerDialog,
    ApplyToPlayerDialog,
  },
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
        }
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

<style lang="stylus">
.empty-msg
  text-align center
  font-size 16px
  padding 10px 0

.texture-name
  width 65%
  display inline-block
  overflow hidden
  text-overflow ellipsis
  white-space nowrap

  small
    font-size 75%

.item-footer > .dropdown-menu
  margin-left 180px

.box-title
  a
    color #6d6d6d

  a.selected
    color #3c8dbc

.breadcrumb
  a
    margin-right 10px
    color #444

  a:hover
    color #3c8dbc
</style>
