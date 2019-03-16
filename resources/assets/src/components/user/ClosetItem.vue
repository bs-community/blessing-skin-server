<template>
  <div class="item" :class="{ 'item-selected': selected }">
    <div class="item-body" @click="$emit('select')">
      <img :src="previewLink">
    </div>
    <div class="item-footer">
      <p class="texture-name">
        <span :title="name">{{ textureName }} <small>({{ type }})</small></span>
      </p>

      <a
        :href="linkToSkinlib"
        :title="$t('user.viewInSkinlib')"
        class="more"
        data-toggle="tooltip"
        data-placement="bottom"
      ><i class="fas fa-share" /></a>
      <span
        id="more-button"
        :title="$t('general.more')"
        class="more"
        data-toggle="dropdown"
        aria-haspopup="true"
      ><i class="fas fa-cog" /></span>

      <ul class="dropup dropdown-menu" aria-labelledby="more-button">
        <li><a v-t="'user.renameItem'" @click="rename" /></li>
        <li><a v-t="'user.removeItem'" @click="remove" /></li>
        <li><a v-if="type !== 'cape'" v-t="'user.setAsAvatar'" @click="setAsAvatar" /></li>
      </ul>
    </div>
  </div>
</template>

<script>
import toastr from 'toastr'
import { swal } from '../../js/notify'
import setAsAvatar from '../mixins/setAsAvatar'

export default {
  name: 'ClosetItem',
  mixins: [setAsAvatar],
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
      const { value: newTextureName, dismiss } = await swal({
        title: this.$t('user.renameClosetItem'),
        input: 'text',
        inputValue: this.textureName,
        showCancelButton: true,
        inputValidator: value => !value && this.$t('skinlib.emptyNewTextureName'),
      })
      if (dismiss) {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/closet/rename',
        { tid: this.tid, new_name: newTextureName }
      )
      if (errno === 0) {
        this.textureName = newTextureName
        toastr.success(msg)
      } else {
        toastr.warning(msg)
      }
    },
    async remove() {
      const { dismiss } = await swal({
        text: this.$t('user.removeFromClosetNotice'),
        type: 'warning',
        showCancelButton: true,
      })
      if (dismiss) {
        return
      }

      const { errno, msg } = await this.$http.post(
        '/user/closet/remove',
        { tid: this.tid }
      )
      if (errno === 0) {
        this.$emit('item-removed')
        swal({ type: 'success', text: msg })
      } else {
        toastr.warning(msg)
      }
    },
  },
}
</script>
