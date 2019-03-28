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
        <previewer closet-mode :skin="skinUrl" :cape="capeUrl">
          <template slot="footer">
            <button
              v-t="'user.useAs'"
              class="btn btn-primary"
              data-toggle="modal"
              data-target="#modal-use-as"
              @click="applyTexture"
            />
            <button
              v-t="'user.resetSelected'"
              class="btn btn-default pull-right"
              @click="resetSelected"
            />
          </template>
        </previewer>
      </div>
    </div>

    <div
      id="modal-use-as"
      class="modal fade"
      tabindex="-1"
      role="dialog"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 v-t="'user.closet.use-as.title'" class="modal-title" />
          </div>
          <div class="modal-body">
            <template v-if="players.length !== 0">
              <div v-for="player in players" :key="player.pid" class="player-item">
                <label class="model-label" :for="player.pid">
                  <input
                    v-model="selectedPlayer"
                    type="radio"
                    name="player"
                    :value="player.pid"
                  >
                  <img :src="avatarUrl(player)" width="35" height="35">
                  <span>{{ player.name }}</span>
                </label>
              </div>
            </template>
            <p v-else v-t="'user.closet.use-as.empty'" />
          </div>
          <div class="modal-footer">
            <a v-t="'user.closet.use-as.add'" href="./player" class="btn btn-default pull-left" />
            <a v-t="'general.submit'" class="btn btn-primary" @click="submitApplyTexture" />
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </section><!-- /.content -->
</template>

<script>
import Paginate from 'vuejs-paginate'
import { debounce, queryString } from '../../scripts/utils'
import ClosetItem from '../../components/ClosetItem.vue'
import EmailVerification from '../../components/EmailVerification.vue'

export default {
  name: 'Closet',
  components: {
    Paginate,
    ClosetItem,
    Previewer: () => import('../../components/Previewer.vue'),
    EmailVerification,
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
    selectedCape: 0,
    capeUrl: '',
    players: [],
    selectedPlayer: 0,
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
      this.applyTexture()
      $('#modal-use-as').modal()
    }
  },
  methods: {
    /* istanbul ignore next */
    search() {}, // eslint-disable-line no-empty-function
    async loadCloset(page = 1) {
      const {
        items, category, total_pages: totalPages,
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
    },
    removeSkinItem(index) {
      this.$delete(this.skinItems, index)
    },
    removeCapeItem(index) {
      this.$delete(this.capeItems, index)
    },
    switchCategory() {
      this.category = this.category === 'skin' ? 'cape' : 'skin'
      this.loadCloset()
    },
    pageChanged(page) {
      this.loadCloset(page)
    },
    avatarUrl(player) {
      return `${blessing.base_url}/avatar/35/${player.tid_skin}`
    },
    async selectTexture(tid) {
      const { type, hash } = await this.$http.get(`/skinlib/info/${tid}`)
      if (type === 'cape') {
        this.capeUrl = `/textures/${hash}`
        this.selectedCape = tid
      } else {
        this.skinUrl = `/textures/${hash}`
        this.selectedSkin = tid
      }
    },
    async applyTexture() {
      this.players = await this.$http.get('/user/player/list')
    },
    async submitApplyTexture() {
      if (!this.selectedPlayer) {
        return this.$message.info(this.$t('user.emptySelectedPlayer'))
      }

      if (!this.selectedSkin && !this.selectedCape) {
        return this.$message.info(this.$t('user.emptySelectedTexture'))
      }

      const { errno, msg } = await this.$http.post(
        '/user/player/set',
        {
          pid: this.selectedPlayer,
          tid: {
            skin: this.selectedSkin || undefined,
            cape: this.selectedCape || undefined,
          },
        }
      )
      if (errno === 0) {
        this.$message.success(msg)
        $('#modal-use-as').modal('hide')
      } else {
        this.$message.warning(msg)
      }
    },
    resetSelected() {
      this.selectedSkin = 0
      this.selectedCape = 0
      this.skinUrl = ''
      this.capeUrl = ''
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

.player-item:not(:nth-child(1))
  margin-top 10px

.breadcrumb
  a
    margin-right 10px
    color #444

  a:hover
    color #3c8dbc
</style>
