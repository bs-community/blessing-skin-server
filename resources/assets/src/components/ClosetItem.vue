<template>
  <div class="card mr-3 mb-3 closet-item" :class="{ shadow: selected }">
    <div class="card-body texture-img" @click="$emit('select')">
      <img class="card-img-top" :src="previewLink">
    </div>
    <div class="card-footer pb-2 pt-2 pl-1 pr-1">
      <div class="container d-flex justify-content-between">
        <span data-test="name" :title="name">
          {{ textureName }} <small>({{ type }})</small>
        </span>

        <a class="float-right dropdown">
          <span
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <i class="fas fa-cog text-gray" />
          </span>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="#" @click="rename">
              {{ $t('user.renameItem') }}
            </a>
            <a class="dropdown-item" href="#" @click="removeClosetItem">
              {{ $t('user.removeItem') }}
            </a>
            <a :href="linkToSkinlib" class="dropdown-item">
              {{ $t('user.viewInSkinlib') }}
            </a>
            <a
              v-if="type !== 'cape'"
              class="dropdown-item"
              href="#"
              @click="setAsAvatar"
            >{{ $t('user.setAsAvatar') }}</a>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import setAsAvatar from './mixins/setAsAvatar'
import removeClosetItem from './mixins/removeClosetItem'
import { showModal, toast } from '../scripts/notify'
import { truthy } from '../scripts/validators'

export default {
  name: 'ClosetItem',
  mixins: [
    removeClosetItem,
    setAsAvatar,
  ],
  props: {
    tid: {
      type: Number,
      required: true,
    },
    type: {
      type: String,
      validator: value => ['steve', 'alex', 'cape'].includes(value),
    },
    name: {
      type: String,
      required: true,
    },
    selected: Boolean,
  },
  data() {
    return {
      textureName: this.name,
    }
  },
  computed: {
    previewLink() {
      return `${blessing.base_url}/preview/${this.tid}.png`
    },
    linkToSkinlib() {
      return `${blessing.base_url}/skinlib/show/${this.tid}`
    },
  },
  methods: {
    async rename() {
      let newTextureName
      try {
        ({ value: newTextureName } = await showModal(
          {
            mode: 'prompt',
            text: this.$t('user.renameClosetItem'),
            input: this.textureName,
            validator: truthy(this.$t('skinlib.emptyNewTextureName')),
          },
        ))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        `/user/closet/rename/${this.tid}`,
        { name: newTextureName },
      )
      if (code === 0) {
        this.textureName = newTextureName
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.closet-item
  width 235px
  transition-property box-shadow
  transition-duration 0.3s
  &:hover
    box-shadow 0 .5rem 1rem rgba(0,0,0,.15)
    cursor pointer

  .fa-cog:hover
    color #000

.texture-img
  background #eff1f0
</style>
