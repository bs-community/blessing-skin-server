<template>
  <div class="container-fluid">
    <vue-good-table
      mode="remote"
      :rows="players"
      :total-rows="totalRecords || players.length"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
      @on-page-change="onPageChange"
      @on-sort-change="onSortChange"
      @on-search="onSearch"
      @on-per-page-change="onPerPageChange"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'name'">
          {{ props.formattedRow[props.column.field] }}
          <a :title="$t('admin.changePlayerName')" data-test="name" @click="changeName(props.row)">
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'uid'">
          <a
            :href="`${baseUrl}/admin/users?uid=${props.row.uid}`"
            :title="$t('admin.inspectHisOwner')"
            data-toggle="tooltip"
            data-placement="right"
          >{{ props.formattedRow[props.column.field] }}</a>
          <a :title="$t('admin.changeOwner')" data-test="owner" @click="changeOwner(props.row)">
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'preview'">
          <a
            v-if="props.row.tid_skin"
            :href="`${baseUrl}/skinlib/show/${props.row.tid_skin}`"
          >
            <img :src="`${baseUrl}/preview/${props.row.tid_skin}/64`" width="64">
          </a>
          <a
            v-if="props.row.tid_cape"
            :href="`${baseUrl}/skinlib/show/${props.row.tid_cape}`"
          >
            <img :src="`${baseUrl}/preview/${props.row.tid_cape}/64`" width="64">
          </a>
        </span>
        <span v-else-if="props.column.field === 'operations'">
          <button
            data-toggle="modal"
            data-target="#modal-change-texture"
            class="btn btn-default"
            @click="textureChanges.originalIndex = props.row.originalIndex"
          >
            {{ $t('admin.changeTexture') }}
          </button>
          <button class="btn btn-danger" @click="deletePlayer(props.row)">
            {{ $t('admin.deletePlayer') }}
          </button>
        </span>
        <span v-else v-text="props.formattedRow[props.column.field]" />
      </template>
    </vue-good-table>

    <modal
      id="modal-change-texture"
      :title="$t('admin.changeTexture')"
      center
      @confirm="changeTexture"
    >
      <div class="form-group">
        <label v-t="'admin.textureType'" />
        <select v-model="textureChanges.type" class="form-control">
          <option v-t="'general.skin'" value="skin" />
          <option v-t="'general.cape'" value="cape" />
        </select>
      </div>
      <div class="form-group">
        <label>TID</label>
        <input
          v-model.number="textureChanges.tid"
          class="form-control"
          type="text"
          :placeholder="$t('admin.pidNotice')"
        >
      </div>
    </modal>
  </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import Modal from '../../components/Modal.vue'
import tableOptions from '../../components/mixins/tableOptions'
import serverTable from '../../components/mixins/serverTable'
import emitMounted from '../../components/mixins/emitMounted'
import { showModal, toast } from '../../scripts/notify'
import { truthy } from '../../scripts/validators'

export default {
  name: 'PlayersManagement',
  components: {
    Modal,
    VueGoodTable,
  },
  mixins: [
    emitMounted,
    tableOptions,
    serverTable,
  ],
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      players: [],
      columns: [
        {
          field: 'pid', label: 'PID', type: 'number',
        },
        { field: 'name', label: this.$t('general.player.player-name') },
        {
          field: 'uid', label: this.$t('general.player.owner'), type: 'number', sortable: false,
        },
        {
          field: 'preview', label: this.$t('general.player.previews'), globalSearchDisabled: true, sortable: false,
        },
        {
          field: 'last_modified', label: this.$t('general.player.last-modified'), sortable: false,
        },
        {
          field: 'operations', label: this.$t('admin.operationsTitle'), globalSearchDisabled: true, sortable: false,
        },
      ],
      textureChanges: {
        originalIndex: -1,
        type: 'skin',
        tid: '',
      },
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      const { data, totalRecords } = await this.$http.get(
        `/admin/player-data${location.search}`,
        !location.search && this.serverParams,
      )
      this.totalRecords = totalRecords
      this.players = data
    },
    async changeTexture() {
      const player = this.players[this.textureChanges.originalIndex]
      const { type, tid } = this.textureChanges

      const { code, message } = await this.$http.post(
        '/admin/players?action=texture',
        {
          pid: player.pid, type, tid,
        },
      )
      if (code === 0) {
        player[`tid_${type}`] = tid
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
    async changeName(player) {
      let value
      try {
        ({ value } = await showModal({
          mode: 'prompt',
          text: this.$t('admin.changePlayerNameNotice'),
          input: player.name,
          validator: truthy(this.$t('admin.emptyPlayerName')),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/players?action=name',
        { pid: player.pid, name: value },
      )
      if (code === 0) {
        player.name = value
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
    async changeOwner(player) {
      let value
      try {
        ({ value } = await showModal({
          mode: 'prompt',
          text: this.$t('admin.changePlayerOwner'),
          input: player.uid,
        }))
      } catch {
        return
      }
      value = Number.parseInt(value)

      const { code, message } = await this.$http.post(
        '/admin/players?action=owner',
        { pid: player.pid, uid: value },
      )
      if (code === 0) {
        player.uid = value
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
    async deletePlayer({ pid, originalIndex }) {
      try {
        await showModal({
          text: this.$t('admin.deletePlayerNotice'),
          okButtonType: 'danger',
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/players?action=delete',
        { pid },
      )
      if (code === 0) {
        this.$delete(this.players, originalIndex)
        toast.success(message)
      } else {
        toast.error(message)
      }
    },
  },
}
</script>
