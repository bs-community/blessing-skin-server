<template>
  <div class="content-wrapper">
    <div class="container">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid d-flex justify-content-between flex-wrap">
          <h1>{{ $t('general.skinlib') }}</h1>
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <i class="fas fa-tags" /> {{ $t('skinlib.nowShowing') }}
            </li>
            <li class="breadcrumb-item">
              <span v-if="filter === 'cape'" v-t="'general.cape'" />
              <span v-else>
                {{ $t('general.skin') }}
                {{ $t('skinlib.filter.' + filter) }}
              </span>
            </li>
            <li class="breadcrumb-item">{{ uploaderIndicator }}</li>
            <li class="breadcrumb-item active">{{ sortIndicator }}</li>
          </ol>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <div class="card">
          <div class="card-body">
            <div class="form-group pt-0 mb-3 d-flex justify-content-between">
              <form @submit.prevent="submitSearch">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <button
                      class="btn btn-default dropdown-toggle"
                      type="button"
                      data-toggle="dropdown"
                    >
                      {{ filterText }}
                    </button>
                    <div class="dropdown-menu">
                      <button
                        class="dropdown-item"
                        :class="{ active: filter === 'skin' }"
                        @click="filter = 'skin'"
                      >
                        {{ $t('general.skin') }}
                      </button>
                      <button
                        class="dropdown-item"
                        :class="{ active: filter === 'steve' }"
                        @click="filter = 'steve'"
                      >
                        Steve
                      </button>
                      <button
                        class="dropdown-item"
                        :class="{ active: filter === 'alex' }"
                        @click="filter = 'alex'"
                      >
                        Alex
                      </button>
                      <button
                        class="dropdown-item"
                        :class="{ active: filter === 'cape' }"
                        @click="filter = 'cape'"
                      >
                        {{ $t('general.cape') }}
                      </button>
                    </div>
                  </div>
                  <input
                    v-model="keyword"
                    class="form-control"
                    type="text"
                    data-test="keyword"
                    :placeholder="$t('vendor.datatable.search')"
                  >
                  <div class="input-group-append">
                    <button
                      class="btn btn-primary pl-3 pr-3"
                      data-test="btn-search"
                      @click="submitSearch"
                    >
                      {{ $t('general.submit') }}
                    </button>
                  </div>
                </div>
              </form>

              <div class="d-none d-sm-block">
                <div class="btn-group">
                  <button
                    class="btn bg-olive"
                    :class="{ active: sort === 'likes' }"
                    @click="sort = 'likes'"
                  >
                    {{ $t('skinlib.sort.likes') }}
                  </button>
                  <button
                    class="btn bg-olive"
                    :class="{ active: sort === 'time' }"
                    @click="sort = 'time'"
                  >
                    {{ $t('skinlib.sort.time') }}
                  </button>
                  <button
                    class="btn bg-olive"
                    :class="{ active: uploader === currentUid }"
                    @click="uploader = currentUid"
                  >
                    {{ $t('skinlib.seeMyUpload') }}
                  </button>
                  <button class="btn bg-olive" @click="reset">
                    {{ $t('skinlib.reset') }}
                  </button>
                </div>
              </div>
            </div>

            <div v-if="items.length" class="d-flex flex-wrap">
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
            </div>
            <p v-else class="text-center m-5">
              {{ $t('general.noResult') }}
            </p>
          </div>

          <div class="box-footer">
            <paginate
              v-model="page"
              :page-count="totalPages"
              class="float-right mr-3"
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

          <div v-show="pending" class="overlay">
            <span>
              <i class="fas fa-sync-alt fa-spin" />
              {{ $t('general.loading') }}
            </span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import Paginate from 'vuejs-paginate'
import { queryString, queryStringify } from '../../scripts/utils'
import SkinLibItem from '../../components/SkinLibItem.vue'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'SkinLibrary',
  components: {
    Paginate,
    SkinLibItem,
  },
  mixins: [
    emitMounted,
  ],
  data() {
    return {
      filter: queryString('filter', 'skin'),
      uploader: +queryString('uploader', 0),
      sort: queryString('sort', 'time'),
      keyword: queryString('keyword', ''),
      page: +queryString('page', 1),
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
    filterText() {
      switch (this.filter) {
        case 'steve':
          return 'Steve'
        case 'alex':
          return 'Alex'
        default:
          return this.$t(`general.${this.filter}`)
      }
    },
    sortIndicator() {
      return this.$t(`skinlib.sort.${this.sort}`)
    },
  },
  watch: {
    filter() {
      this.fetchData()
      this.updateQueryString()
    },
    uploader() {
      this.fetchData()
      this.updateQueryString()
    },
    sort() {
      this.fetchData()
      this.updateQueryString()
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
    updateQueryString() {
      const qs = queryStringify({
        filter: this.filter,
        uploader: this.uploader,
        sort: this.sort,
        keyword: this.keyword,
        page: this.page,
      })
      window.history.pushState(null, '', `skinlib?${qs}`)
    },
    submitSearch() {
      this.fetchData()
      this.updateQueryString()
    },
    pageChanged(page) {
      this.page = page
      this.fetchData()
      this.updateQueryString()
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
</style>
