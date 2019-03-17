<template>
  <a :href="urlToDetail">
    <div class="item">
      <div class="item-body">
        <img :src="urlToPreview">
      </div>

      <div class="item-footer">
        <p class="texture-name">
          <span :title="name">{{ name }}
            <small>{{ $t('skinlib.filter.' + type) }}</small>
          </span>
        </p>

        <a
          :title="likeActionText"
          class="more like"
          :class="{ liked, anonymous }"
          href="#"
          @click.stop="toggleLiked"
        >
          <i class="fas fa-heart" />
          <span>{{ likes }}</span>
        </a>

        <small v-if="!isPublic" class="more private-label">
          {{ $t('skinlib.private') }}
        </small>
      </div>
    </div>
  </a>
</template>

<script>
import addClosetItem from '../mixins/addClosetItem'
import removeClosetItem from '../mixins/removeClosetItem'

export default {
  name: 'SkinLibItem',
  mixins: [
    addClosetItem,
    removeClosetItem,
  ],
  props: {
    tid: Number,
    name: String,
    type: {
      validator: value => ['steve', 'alex', 'cape'].includes(value),
    },
    liked: Boolean,
    likes: Number,
    anonymous: Boolean,
    isPublic: Boolean, // `public` is a reserved keyword
  },
  computed: {
    urlToDetail() {
      return `${blessing.base_url}/skinlib/show/${this.tid}`
    },
    urlToPreview() {
      return `${blessing.base_url}/preview/${this.tid}.png`
    },
    likeActionText() {
      if (this.anonymous) {
        return this.$t('skinlib.anonymous')
      }

      return this.liked
        ? this.$t('skinlib.removeFromCloset')
        : this.$t('skinlib.addToCloset')
    },
  },
  methods: {
    toggleLiked() {
      if (this.anonymous) {
        return
      }

      if (this.liked) {
        this.removeFromCloset()
      } else {
        this.addClosetItem()
      }
    },
    async removeFromCloset() {
      this.$once('item-removed', () => this.$emit('like-toggled', false))
      await this.removeClosetItem()
    },
  },
}
</script>

<style lang="stylus">
.texture-name
  width 65%
  display inline-block
  overflow hidden
  text-overflow ellipsis
  white-space nowrap

@media (min-width: 1200px)
  .item
    width 250px

  .item-body > img
    margin-left 50px

  .texture-name
    width 65%

.item-footer
  a
    color #fff

  .like:hover, .liked
    color #e0353b

.private-label
  margin-top 2px
</style>
