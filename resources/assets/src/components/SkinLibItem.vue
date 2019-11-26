<template>
  <a class="ml-3 mr-2 mb-2" :href="urlToDetail">
    <div class="card skinlib-item">
      <div class="card-body texture-img">
        <div v-if="!isPublic" class="ribbon-wrapper">
          <div class="ribbon bg-pink">{{ $t('skinlib.private') }}</div>
        </div>
        <img class="card-img-top" :src="urlToPreview">
      </div>
      <div class="card-footer pb-0 pt-2 pl-1 pr-1">
        <div class="container d-flex justify-content-between">
          <p>
            <span :title="name">{{ name }}
              <small>{{ $t('skinlib.filter.' + type) }}</small>
            </span>
          </p>

          <a
            :title="likeActionText"
            class="btn-like"
            :class="{ liked }"
            href="#"
            @click.stop="toggleLiked"
          >
            <i class="fas fa-heart" />
            <span>{{ likes }}</span>
          </a>
        </div>

      </div>
    </div>
  </a>
</template>

<script>
import addClosetItem from './mixins/addClosetItem'
import removeClosetItem from './mixins/removeClosetItem'

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
      return `${document.baseURI}skinlib/show/${this.tid}`
    },
    urlToPreview() {
      return `${document.baseURI}preview/${this.tid}.png`
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
.skinlib-item
  width 245px
  transition-property box-shadow
  transition-duration 0.3s
  &:hover
    box-shadow 0 .5rem 1rem rgba(0, 0, 0, 0.15)

.texture-img
  background #eff1f0

.btn-like
  color #6c757d
  &.liked, &:hover
    color #e0353b
</style>
