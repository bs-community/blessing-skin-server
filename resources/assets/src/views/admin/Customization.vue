<template>
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 v-t="'admin.change-color.title'" class="box-title" />
    </div>
    <div class="box-body no-padding">
      <table class="table table-striped bring-up nth-2-center">
        <tbody>
          <template v-for="color in colors">
            <tr
              v-for="innerColor in [color, `${color}-light`]"
              :key="innerColor"
            >
              <td v-t="`admin.colors.${innerColor}`" />
              <td>
                <a
                  href="#"
                  :class="`btn bg-${color} btn-xs`"
                  @click="preview(innerColor)"
                >
                  <i class="far fa-eye" />
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="box-footer">
      <button v-t="'general.submit'" class="btn btn-primary" @click="submit" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'Customization',
  data() {
    return {
      colors: ['blue', 'yellow', 'green', 'purple', 'red', 'black'],
      currentSkin: blessing.extra.currentSkin,
    }
  },
  methods: {
    preview(color) {
      document.body.classList.replace(this.currentSkin, `skin-${color}`)
      this.currentSkin = `skin-${color}`
    },
    async submit() {
      const { msg } = await this.$http.post('/admin/customize?action=color', {
        color_scheme: this.currentSkin,
      })
      this.$message.success(msg)
    },
  },
}
</script>
