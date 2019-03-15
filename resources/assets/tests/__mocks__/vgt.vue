<template>
  <div class="vgt-table">
    <input
      v-model="search"
      class="vgt-input"
      @input="$emit('on-search', { searchTerm: search })"
    >
    <div v-if="rows.length === 0">No data</div>
    <table v-else>
      <tbody>
        <tr v-for="(row, i) in rows" :key="i" :class="getRowClass(row)">
          <td v-for="col in columns" :key="col.field">
            <slot
              name="table-row"
              :row="Object.assign(row, { originalIndex: i })"
              :column="{ field: col.field }"
              :formattedRow="{ [col.field]: row[col.field] }"
            />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'VueGoodTable',
  // eslint-disable-next-line vue/require-prop-types
  props: ['rows', 'columns', 'rowStyleClass'],
  data() {
    return {
      search: '',
    }
  },
  methods: {
    getRowClass(row) {
      if (typeof this.rowStyleClass === 'function') {
        return { [this.rowStyleClass(row)]: true }
      } else if (typeof this.rowStyleClass === 'string') {
        return { [this.rowStyleClass]: true }
      }
      return {}
    },
  },
}
</script>
