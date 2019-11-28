<template>
  <div
    :id="id"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
  >
    <div class="modal-dialog" :class="{ 'modal-dialog-centered': center }" role="document">
      <div class="modal-content" :class="[`bg-${type}`]">
        <div v-if="showHeader" class="modal-header">
          <h5 class="modal-title">{{ title }}</h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Close"
            @click="dismiss"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <slot>
            <template v-if="text">
              <p v-for="(line, i) in lines" :key="i">{{ line }}</p>
            </template>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-else-if="dangerousHTML" v-html="dangerousHTML" />
            <template v-if="mode === 'prompt'">
              <div class="form-group">
                <input v-model="value" type="text" class="form-control">
              </div>
            </template>
          </slot>
        </div>
        <div class="modal-footer" :class="footerClasses">
          <slot name="footer">
            <button
              v-if="mode !== 'alert'"
              type="button"
              class="btn"
              :class="[`btn-${cancelButtonType}`]"
              data-dismiss="modal"
              @click="dismiss"
            >
              {{ cancelButtonText }}
            </button>
            <button
              type="button"
              class="btn"
              :class="[`btn-${okButtonType}`]"
              @click="confirm"
            >
              {{ okButtonText }}
            </button>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import $ from 'jquery'
import { trans } from '../scripts/i18n'

export default {
  name: 'Modal',
  props: {
    mode: {
      type: String,
      default: 'confirm',
      validator: value => ['alert', 'confirm', 'prompt'].includes(value),
    },
    id: {
      type: String,
    },
    title: {
      type: String,
      default: trans('general.tip'),
    },
    text: {
      type: String,
      default: '',
    },
    dangerousHTML: {
      type: String,
    },
    input: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: 'default',
    },
    showHeader: {
      type: Boolean,
      default: true,
    },
    center: {
      type: Boolean,
      default: false,
    },
    okButtonText: {
      type: String,
      default: trans('general.confirm'),
    },
    okButtonType: {
      type: String,
      default: 'primary',
    },
    cancelButtonText: {
      type: String,
      default: trans('general.cancel'),
    },
    cancelButtonType: {
      type: String,
      default: 'secondary',
    },
    flexFooter: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      hidden: false,
      value: this.input,
    }
  },
  computed: {
    lines() {
      return this.text.split(/\r?\n/)
    },
    footerClasses() {
      return {
        'd-flex': this.flexFooter,
        'justify-content-between': this.flexFooter,
      }
    },
  },
  mounted() {
    $(this.$el)
      .on('hide.bs.modal', () => {
        if (!this.hidden) {
          this.dismiss()
        }
      })
      .on('hidden.bs.modal', () => {
        this.hidden = false
      })
  },
  methods: {
    confirm() {
      this.hidden = true
      this.$emit('confirm', { value: this.value })
      $(this.$el).modal('hide')
    },
    dismiss() {
      this.hidden = true
      this.$emit('dismiss')
    },
  },
}
</script>
