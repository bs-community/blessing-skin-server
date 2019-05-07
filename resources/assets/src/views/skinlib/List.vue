<template>
  <div class="content-wrapper">
    <div class="container">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>{{ $t('general.skinlib') }}</h1>
        <ol class="breadcrumb">
          <li><i class="fas fa-tags" /> {{ $t('skinlib.nowShowing') }}</li>
          <li>
            <span v-if="filter === 'cape'" v-t="'general.cape'" />
            <span v-else>
              {{ $t('general.skin') }}
              {{ $t('skinlib.filter.' + filter) }}
            </span>
          </li>
          <li>{{ uploaderIndicator }}</li>
          <li class="active">{{ sortIndicator }}</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="box box-default">
          <div class="box-header">
            <div class="form-group row">
              <form class="col-md-6" @submit.prevent="fetchData">
                <el-input
                  v-model="keyword"
                  :placeholder="$t('vendor.datatable.search')"
                  clearable
                >
                  <template #prepend>
                    <el-select v-model="filter" class="texture-type-select">
                      <el-option :label="$t('general.skin')" value="skin" />
                      <el-option label="Steve" value="steve" />
                      <el-option label="Alex" value="alex" />
                      <el-option :label="$t('general.cape')" value="cape" />
                    </el-select>
                  </template>
                  <template #append>
                    <el-button data-test="btn-search" @click="fetchData">
                      {{ $t('general.submit') }}
                    </el-button>
                  </template>
                </el-input>
              </form>

              <div class="col-md-6 advanced-filter">
                <el-button-group class="pull-right">
                  <el-button plain @click="sort = 'likes'">
                    {{ $t('skinlib.sort.likes') }}
                  </el-button>
                  <el-button plain @click="sort = 'time'">
                    {{ $t('skinlib.sort.time') }}
                  </el-button>
                  <el-button plain @click="uploader = currentUid">
                    {{ $t('skinlib.seeMyUpload') }}
                  </el-button>
                  <el-button plain @click="reset">
                    {{ $t('skinlib.reset') }}
                  </el-button>
                </el-button-group>
              </div>
            </div>
          </div>

          <!-- Container of Skin Library -->
          <div class="box-body">
            <template v-if="items.length">
              <skin-lib-item
                v-for="(item, index) in items"
                :key="item.tid"
                :tid="item.tid"
                :name="item.name"
                :type="item.type"
                :liked="item.liked"
                :likes="item.likes"
                :is-public="item.public"
                :anonymous="anonymous"
                @like-toggled="onLikeToggled(index, $event)"
              />
            </template>
            <p v-else style="text-align: center; margin: 30px 0;">
              {{ $t('general.noResult') }}
            </p>
          </div>

          <div class="box-footer">
            <paginate
              v-model="page"
              :page-count="totalPages"
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

          <div v-show="pending" class="overlay">
            <span>
              <i class="fas fa-sync-alt fa-spin" />
              {{ $t('general.loading') }}
            </span>
          </div>
        </div><!-- /.box -->
      </section><!-- /.content -->
    </div><!-- /.container -->
  </div><!-- /.content-wrapper -->
</template>

<script>
import Vue from 'vue'
import Paginate from 'vuejs-paginate'
import {
  ButtonGroup, Select, Option,
} from 'element-ui'
import { queryString } from '../../scripts/utils'
import SkinLibItem from '../../components/SkinLibItem.vue'

Vue.use(ButtonGroup)
Vue.use(Select)
Vue.use(Option)

export default {
  name: 'SkinLibrary',
  components: {
    Paginate,
    SkinLibItem,
  },
  data() {
    return {
      filter: queryString('filter', 'skin'),
      uploader: +queryString('uploader', 0),
      sort: queryString('sort', 'time'),
      keyword: queryString('keyword', ''),
      page: 1,
      items: [],
      totalPages: 0,
      currentUid: 0,
      pending: false,
    }
  },
  computed: {
    anonymous() {
      return !this.currentUid
    },
    uploaderIndicator() {
      return this.uploader
        ? this.$t('skinlib.filter.uploader', { uid: this.uploader })
        : this.$t('skinlib.filter.allUsers')
    },
    sortIndicator() {
      return this.$t(`skinlib.sort.${this.sort}`)
    },
  },
  watch: {
    filter() {
      this.fetchData()
    },
    uploader() {
      this.fetchData()
    },
    sort() {
      this.fetchData()
    },
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.pending = true
      const {
        data: {
          items, total_pages: totalPages, current_uid: currentUid,
        },
      } = await this.$http.get(
        '/skinlib/data',
        {
          filter: this.filter,
          uploader: this.uploader,
          sort: this.sort,
          keyword: this.keyword,
          page: this.page,
        }
      )
      this.items = items
      this.totalPages = totalPages
      this.currentUid = currentUid
      this.pending = false
    },
    pageChanged(page) {
      this.page = page
      this.fetchData()
    },
    reset() {
      this.filter = 'skin'
      this.uploader = 0
      this.sort = 'time'
      this.keyword = ''
      this.page = 1
    },
    onLikeToggled(index, action) {
      this.items[index].liked = action
      this.items[index].likes += action ? 1 : -1
    },
  },
}
</script>

<style lang="stylus">
.overlay span
  position absolute
  top 50%
  left 50%
  margin-left -40px
  margin-top 25px
  color #000
  font-size 20px

.texture-type-select
  width 90px

.advanced-filter
  @media (max-width 850px)
    display none

.btn-tail
  margin-left 6px
</style>
