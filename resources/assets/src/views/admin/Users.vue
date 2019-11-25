<template>
  <div class="container-fluid">
    <vue-good-table
      mode="remote"
      :rows="users"
      :total-rows="totalRecords || users.length"
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
        <span v-if="props.column.field === 'email'">
          {{ props.formattedRow[props.column.field] }}
          <a :title="$t('admin.changeEmail')" data-test="email" @click="changeEmail(props.row)">
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'nickname'">
          {{ props.formattedRow[props.column.field] }}
          <a :title="$t('admin.changeNickName')" data-test="nickname" @click="changeNickName(props.row)">
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'score'">
          {{ props.formattedRow[props.column.field] }}
          <a :title="$t('admin.changeScore')" data-test="score" @click="changeScore(props.row)">
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'players_count'">
          <a
            :href="props.row | playersLink"
            :title="$t('admin.inspectHisPlayers')"
            data-toggle="tooltip"
            data-placement="right"
          >{{ props.formattedRow[props.column.field] }}</a>
        </span>
        <span v-else-if="props.column.field === 'permission'">
          <span>{{ props.row | humanizePermission }}</span>
          <a
            v-if="props.row.permission < 1 || (props.row.operations === 2 && props.row.permission < 2)"
            :title="$t('admin.changePermission')"
            data-test="permission"
            @click="changePermission(props.row)"
          >
            <i class="fas fa-edit btn-edit" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'verified'">
          <span v-if="props.row.verified" v-t="'admin.verified'" />
          <span v-else v-t="'admin.unverified'" />
          <a
            :title="$t('admin.toggleVerification')"
            data-test="verification"
            @click="toggleVerification(props.row)"
          >
            <i
              class="fas btn-edit"
              :class="{ 'fa-toggle-on': props.row.verified, 'fa-toggle-off': !props.row.verified }"
            />
          </a>
        </span>
        <div v-else-if="props.column.field === 'operations'">
          <button class="btn btn-default" @click="changePassword(props.row)">
            {{ $t('admin.changePassword') }}
          </button>
          <button
            :disabled="props.row.permission >= 2 || (props.row.operations === 1 && props.row.permission >= 1)"
            class="btn btn-danger"
            data-test="deleteUser"
            @click="deleteUser(props.row)"
          >
            {{ $t('admin.deleteUser') }}
          </button>
        </div>
        <span v-else v-text="props.formattedRow[props.column.field]" />
      </template>
    </vue-good-table>
  </div>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'
import { trans } from '../../scripts/i18n'
import tableOptions from '../../components/mixins/tableOptions'
import serverTable from '../../components/mixins/serverTable'
import emitMounted from '../../components/mixins/emitMounted'

export default {
  name: 'UsersManagement',
  components: {
    VueGoodTable,
  },
  filters: {
    humanizePermission(user) {
      switch (user.permission) {
        case -1:
          return trans('admin.banned')
        case 0:
          return trans('admin.normal')
        case 1:
          return trans('admin.admin')
        case 2:
          return trans('admin.superAdmin')
        default:
          return ''
      }
    },
    playersLink(user) {
      return `${blessing.base_url}/admin/players?uid=${user.uid}`
    },
  },
  mixins: [
    emitMounted,
    tableOptions,
    serverTable,
  ],
  data() {
    return {
      users: [],
      columns: [
        {
          field: 'uid', label: 'UID', type: 'number',
        },
        { field: 'email', label: this.$t('general.user.email') },
        {
          field: 'nickname', label: this.$t('general.user.nickname'), width: '150px',
        },
        {
          field: 'score', label: this.$t('general.user.score'), type: 'number', width: '102px',
        },
        {
          field: 'players_count', label: this.$t('admin.playersCount'), type: 'number', sortable: false,
        },
        {
          field: 'permission', label: this.$t('admin.permission'), globalSearchDisabled: true,
        },
        {
          field: 'verified', label: this.$t('admin.verification'), type: 'boolean', globalSearchDisabled: true,
        },
        { field: 'register_at', label: this.$t('general.user.register-at') },
        {
          field: 'operations', label: this.$t('admin.operationsTitle'), sortable: false, globalSearchDisabled: true,
        },
      ],
    }
  },
  beforeMount() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      const { data, totalRecords } = await this.$http.get(
        `/admin/user-data${location.search}`,
        !location.search && this.serverParams,
      )
      this.totalRecords = totalRecords
      this.users = data
    },
    async changeEmail(user) {
      let value
      try {
        ({ value } = await this.$prompt(this.$t('admin.newUserEmail'), {
          inputValue: user.email,
          inputValidator: val => !!val || this.$t('auth.emptyEmail'),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/users?action=email',
        { uid: user.uid, email: value },
      )
      if (code === 0) {
        user.email = value
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async toggleVerification(user) {
      const { code, message } = await this.$http.post(
        '/admin/users?action=verification',
        { uid: user.uid },
      )
      if (code === 0) {
        user.verified = !user.verified
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async changeNickName(user) {
      let value
      try {
        ({ value } = await this.$prompt(this.$t('admin.newUserNickname'), {
          inputValue: user.nickname,
          inputValidator: val => !!val || this.$t('auth.emptyNickname'),
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/users?action=nickname',
        { uid: user.uid, nickname: value },
      )
      if (code === 0) {
        user.nickname = value
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async changePassword(user) {
      let value
      try {
        ({ value } = await this.$prompt(this.$t('admin.newUserPassword'), {
          inputType: 'password',
        }))
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/users?action=password',
        { uid: user.uid, password: value },
      )
      code === 0 ? this.$message.success(message) : this.$message.warning(message)
    },
    async changeScore(user) {
      let value
      try {
        ({ value } = await this.$prompt(this.$t('admin.newScore'), {
          inputType: 'number',
          inputValue: user.score,
        }))
      } catch {
        return
      }
      const score = Number.parseInt(value)

      const { code, message } = await this.$http.post(
        '/admin/users?action=score',
        { uid: user.uid, score },
      )
      if (code === 0) {
        user.score = score
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async changePermission(user) {
      const operator = user.operations
      const options = [
        this.$t('admin.banned'),
        this.$t('admin.normal'),
      ]
      if (operator === 2) {
        options.push(this.$t('admin.admin'))
      }
      const h = this.$createElement
      const vnode = h('div', null, [
        h('span', null, this.$t('admin.newPermission')),
        h(
          'select',
          { attrs: { selectedIndex: 0 } },
          options.map(option => h('option', null, option)),
        ),
      ])

      try {
        await this.$msgbox({
          message: vnode,
          showCancelButton: true,
        })
      } catch {
        return
      }
      const value = vnode.children[1].elm.selectedIndex - 1

      const { code, message } = await this.$http.post('/admin/users?action=permission', {
        uid: user.uid,
        permission: value,
      })
      if (code === 0) {
        user.permission = +value
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
    async deleteUser({ uid, originalIndex }) {
      try {
        await this.$confirm(this.$t('admin.deleteUserNotice'), {
          type: 'warning',
        })
      } catch {
        return
      }

      const { code, message } = await this.$http.post(
        '/admin/users?action=delete',
        { uid },
      )
      if (code === 0) {
        this.$delete(this.users, originalIndex)
        this.$message.success(message)
      } else {
        this.$message.warning(message)
      }
    },
  },
}
</script>

<style lang="stylus">
.operations-menu
  margin-left -35px

.fa-edit
  cursor pointer

.fa-toggle-on, .fa-toggle-off
  font-size 18px
  cursor pointer

.row-at-bottom
  margin-top -100px
</style>
