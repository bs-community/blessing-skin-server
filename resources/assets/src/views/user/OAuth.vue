<template>
  <div class="container-fluid">
    <button
      type="primary"
      class="btn-create-app btn btn-primary"
      data-toggle="modal"
      data-target="#modal-create"
    >
      {{ $t('user.oauth.create') }}
    </button>
    <vue-good-table
      :rows="clients"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'name'">
          {{ props.formattedRow[props.column.field] }}&nbsp;
          <a
            :title="$t('user.oauth.modifyName')"
            href="#"
            data-test="name"
            @click="modifyName(props.row)"
          >
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'redirect'">
          {{ props.formattedRow[props.column.field] }}&nbsp;
          <a
            :title="$t('user.oauth.modifyUrl')"
            href="#"
            data-test="callback"
            @click="modifyCallback(props.row)"
          >
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'operations'">
          <button class="btn btn-danger" data-test="remove" @click="remove(props.row)">
            {{ $t('report.delete') }}
          </button>
        </span>
        <span v-else>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>

    <div
      id="modal-create"
      class="modal fade"
      tabindex="-1"
      role="dialog"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 v-t="'user.oauth.create'" class="modal-title" />
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <table class="table">
              <tbody>
                <tr>
                  <td v-t="'user.oauth.name'" class="key" />
                  <td class="value">
                    <input v-model="name" class="form-control" type="text">
                  </td>
                </tr>
                <tr>
                  <td v-t="'user.oauth.redirect'" class="key" />
                  <td class="value">
                    <input v-model="callback" class="form-control" type="text">
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer d-flex justify-content-between">
            <button class="btn btn-default" data-dismiss="modal">
              {{ $t('general.close') }}
            </button>
            <button class="btn btn-primary" data-test="create" @click="create">
              {{ $t('general.submit') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import tableOptions from '../../components/mixins/tableOptions'
import emitMounted from '../../components/mixins/emitMounted'
import { walkFetch, init } from '../../scripts/net'

export default {
  name: 'OAuthApps',
  components: {
    VueGoodTable,
  },
  mixins: [
    emitMounted,
    tableOptions,
  ],
  data() {
    return {
      name: '',
      callback: '',
      clients: [],
      columns: [
        {
          field: 'id', label: this.$t('user.oauth.id'), type: 'number',
        },
        { field: 'name', label: this.$t('user.oauth.name') },
        {
          field: 'secret',
          label: this.$t('user.oauth.secret'),
          sortable: false,
          globalSearchDisabled: true,
        },
        {
          field: 'redirect',
          label: this.$t('user.oauth.redirect'),
          sortable: false,
          globalSearchDisabled: true,
        },
        {
          field: 'operations',
          label: this.$t('admin.operationsTitle'),
          sortable: false,
          globalSearchDisabled: true,
        },
      ],
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      this.clients = await this.$http.get('/oauth/clients')
    },
    async create() {
      const client = await this.$http.post('/oauth/clients', {
        name: this.name,
        redirect: this.callback,
      })
      if (client.id) {
        $('#modal-create').modal('hide')
        this.clients.unshift(client)
      } else {
        this.$message.warning(client.message)
      }
    },
    async modifyName(client) {
      let name
      try {
        const { value } = await this.$prompt('', {
          title: this.$t('user.oauth.name'),
          inputValue: client.name,
        })
        name = value
      } catch {
        return
      }
      await this.modify(client, { name })
    },
    async modifyCallback(client) {
      let redirect
      try {
        const { value } = await this.$prompt('', {
          title: this.$t('user.oauth.redirect'),
          inputValue: client.redirect,
        })
        redirect = value
      } catch {
        return
      }
      await this.modify(client, { redirect })
    },
    async modify(client, modified) {
      const request = new Request(
        `/oauth/clients/${client.id}`,
        Object.assign({}, init, {
          body: JSON.stringify(Object.assign({ name: client.name, redirect: client.redirect }, modified)),
          method: 'PUT',
        })
      )
      request.headers.set('Content-Type', 'application/json')
      const result = await walkFetch(request)
      if (result.id) {
        Object.assign(client, modified)
      } else {
        this.$message.warning(result.message)
      }
    },
    async remove(client) {
      try {
        await this.$confirm(this.$t('user.oauth.confirmRemove'), { type: 'warning' })
      } catch {
        return
      }
      const request = new Request(
        `/oauth/clients/${client.id}`,
        Object.assign({}, init, { method: 'DELETE' })
      )
      await walkFetch(request)
      this.$delete(this.clients, this.clients.findIndex(({ id }) => id === client.id))
    },
  },
}
</script>

<style lang="stylus">
.btn-create-app
  margin-bottom 5px
  margin-right 10px
</style>
