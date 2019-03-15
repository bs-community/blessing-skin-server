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
              <div class="col-md-4">
                <label>
                  <input
                    v-model="filter"
                    type="radio"
                    name="filter"
                    value="skin"
                  >
                  {{ $t('skinlib.anyModels') }}
                </label>&nbsp;
                <label>
                  <input
                    v-model="filter"
                    type="radio"
                    name="filter"
                    value="steve"
                  >
                  Steve
                </label>&nbsp;
                <label>
                  <input
                    v-model="filter"
                    type="radio"
                    name="filter"
                    value="alex"
                  >
                  Alex
                </label>&nbsp;
                <label>
                  <input
                    v-model="filter"
                    type="radio"
                    name="filter"
                    value="cape"
                  >
                  {{ $t('general.cape') }}
                </label>
              </div>

              <form class="col-md-4" @submit.prevent="fetchData">
                <div class="input-group">
                  <input
                    v-model="keyword"
                    type="text"
                    :placeholder="$t('vendor.datatable.search')"
                    class="form-control"
                  >
                  <span class="input-group-btn">
                    <button class="btn btn-success" @click="fetchData">
                      {{ $t('general.submit') }}
                    </button>
                  </span>
                </div>
              </form>

              <div class="col-md-4">
                <div class="btn-group filter-btn">
                  <button
                    class="btn btn-primary dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    {{ $t('skinlib.sort.title') }} <span class="caret" />
                  </button>
                  <ul class="dropdown-menu">
                    <li><a v-t="'skinlib.sort.likes'" href="#" @click="sort = 'likes'" /></li>
                    <li><a v-t="'skinlib.sort.time'" href="#" @click="sort = 'time'" /></li>
                  </ul>
                </div>
                <a
                  v-if="currentUid"
                  class="btn btn-default btn-tail"
                  @click="uploader = currentUid"
                >{{ $t('skinlib.seeMyUpload') }}</a>
                <button class="btn btn-warning btn-tail" @click="reset">
                  {{ $t('skinlib.reset') }}
                </button>
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
import Paginate from 'vuejs-paginate'
import { queryString } from '../../js/utils'
import SkinLibItem from './SkinLibItem'

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
    filter(value) {
      $(`[value="${value}"]`).iCheck('check')
      this.fetchData()
    },
    uploader() {
      this.fetchData()
    },
    sort() {
      this.fetchData()
    },
  },
  created() {
    let available = true
    const origin = this.fetchData
    this.fetchData = () => {
      if (available) {
        available = false
        setTimeout(() => (available = true), 50)
        origin()
      }
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.pending = true
      const {
        items, total_pages: totalPages, current_uid: currentUid,
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
.overlay span {
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: -40px;
    margin-top: 25px;
    color: #000;
    font-size: 20px;
}

@media (min-width: 768px) {
    .filter-btn {
        margin-left: 25px;
    }
}

.btn-tail {
    margin-left: 6px;
}
</style>
