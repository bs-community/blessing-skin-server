import React, { useState, useEffect, useLayoutEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { useImmer } from 'use-immer'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useIsLargeScreen from '@/scripts/hooks/useIsLargeScreen'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { User, UserPermission, Paginator } from '@/scripts/types'
import { toast, showModal } from '@/scripts/notify'
import urls from '@/scripts/urls'
import type { Props as ModalInputProps } from '@/components/ModalInput'
import Pagination from '@/components/Pagination'
import Header from './Header'
import Card from './Card'
import LoadingCard from './LoadingCard'
import Row from './Row'
import LoadingRow from './LoadingRow'

const UsersManagement: React.FC = () => {
  const [users, setUsers] = useImmer<User[]>([])
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [isLoading, setIsLoading] = useState(false)
  const isLargeScreen = useIsLargeScreen()
  const [isTableMode, setIsTableMode] = useState(false)
  const [query, setQuery] = useState('')
  const currentUser = useBlessingExtra<User>('currentUser', {
    uid: 0,
    permission: UserPermission.Admin,
  } as User)

  useLayoutEffect(() => {
    if (isLargeScreen) {
      setIsTableMode(true)
    }
  }, [isLargeScreen])

  const getUsers = async () => {
    setIsLoading(true)
    const { data, last_page }: Paginator<User> = await fetch.get(
      urls.admin.users.list(),
      {
        q: query,
        page,
      },
    )
    setUsers(() => data)
    setTotalPages(last_page)
    setIsLoading(false)
  }

  useEffect(() => {
    getUsers()
  }, [page])

  const handleModeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setIsTableMode(event.target.value === 'table')
  }

  const handleQueryChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setQuery(event.target.value)
  }

  const handleSubmitQuery = (event: React.FormEvent) => {
    event.preventDefault()
    getUsers()
  }

  const handleEmailChange = async (user: User, index: number) => {
    let email: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.newUserEmail'),
        input: user.email,
        inputMode: 'email',
        validator: (value: string) => {
          if (!value) {
            return t('auth.emptyEmail')
          }
        },
      })
      email = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.email(user.uid),
      { email },
    )
    if (code === 0) {
      toast.success(message)
      setUsers((users) => {
        users[index].email = email
      })
    } else {
      toast.error(message)
    }
  }

  const handleNicknameChange = async (user: User, index: number) => {
    let nickname: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.newUserNickname'),
        input: user.nickname,
        validator: (value: string) => {
          if (!value) {
            return t('auth.emptyNickname')
          }
        },
      })
      nickname = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.nickname(user.uid),
      { nickname },
    )
    if (code === 0) {
      toast.success(message)
      setUsers((users) => {
        users[index].nickname = nickname
      })
    } else {
      toast.error(message)
    }
  }

  const handleScoreChange = async (user: User, index: number) => {
    let score: number
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.newScore'),
        input: user.score.toString(),
        inputMode: 'numeric',
      })
      score = Number.parseInt(value)
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.score(user.uid),
      { score },
    )
    if (code === 0) {
      toast.success(message)
      setUsers((users) => {
        users[index].score = score
      })
    } else {
      toast.error(message)
    }
  }

  const handlePermissionChange = async (user: User, index: number) => {
    const permissions: ModalInputProps['choices'] = [
      { text: t('admin.banned'), value: '-1' },
      { text: t('admin.normal'), value: '0' },
    ]
    if (currentUser.permission > UserPermission.Admin) {
      permissions.push({ text: t('admin.admin'), value: '1' })
    }

    let permission: UserPermission
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.newPermission'),
        input: user.permission.toString(),
        inputType: 'radios',
        choices: permissions,
      })
      permission = Number.parseInt(value)
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.permission(user.uid),
      { permission },
    )
    if (code === 0) {
      toast.success(message)
      setUsers((users) => {
        users[index].permission = permission
      })
    } else {
      toast.error(message)
    }
  }

  const handleVerificationToggle = async (user: User, index: number) => {
    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.verification(user.uid),
    )
    if (code === 0) {
      toast.success(message)
      setUsers((users) => {
        users[index].verified = !users[index].verified
      })
    } else {
      toast.error(message)
    }
  }

  const handlePasswordChange = async (user: User) => {
    let password: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.newUserPassword'),
        inputType: 'password',
        placeholder: t('admin.changePassword'),
      })
      password = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      urls.admin.users.password(user.uid),
      { password },
    )
    if (code === 0) {
      toast.success(message)
    } else {
      toast.error(message)
    }
  }

  const handleDelete = async (user: User) => {
    try {
      await showModal({
        text: t('admin.deleteUserNotice'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { code, message } = await fetch.del(urls.admin.users.delete(user.uid))
    if (code === 0) {
      toast.success(message)
      setUsers((users) => users.filter(({ uid }) => uid !== user.uid))
    } else {
      toast.error(message)
    }
  }

  return (
    <div className="card">
      <Header className="card-header">
        <form className="input-group" onSubmit={handleSubmitQuery}>
          <input
            type="text"
            inputMode="search"
            className="form-control"
            title={t('vendor.datatable.search')}
            value={query}
            onChange={handleQueryChange}
          />
          <div className="input-group-append">
            <button className="btn btn-primary" type="submit">
              {t('vendor.datatable.search')}
            </button>
          </div>
        </form>
        <div className="btn-group btn-group-toggle">
          <label
            className={`btn btn-secondary ${isTableMode ? 'active' : ''}`}
            title="Table Mode"
          >
            <input
              type="radio"
              value="table"
              checked={isTableMode}
              onChange={handleModeChange}
            />
            <i className="fas fa-list"></i>
          </label>
          <label
            className={`btn btn-secondary ${isTableMode ? '' : 'active'}`}
            title="Card Mode"
          >
            <input
              type="radio"
              value="card"
              checked={!isTableMode}
              onChange={handleModeChange}
            />
            <i className="fas fa-grip-vertical"></i>
          </label>
        </div>
      </Header>
      {users.length === 0 && !isLoading ? (
        <div className="card-body text-center">{t('general.noResult')}</div>
      ) : isTableMode ? (
        <div className="card-body table-responsive p-0">
          <table className={`table ${isLoading ? '' : 'table-striped'}`}>
            <thead>
              <tr>
                <th>UID</th>
                <th>{t('general.user.email')}</th>
                <th>{t('general.user.nickname')}</th>
                <th>{t('general.user.score')}</th>
                <th>{t('admin.permission')}</th>
                <th>{t('admin.verification')}</th>
                <th>{t('general.user.register-at')}</th>
                <th>{t('admin.operationsTitle')}</th>
              </tr>
            </thead>
            <tbody>
              {isLoading
                ? new Array(10).fill(null).map((_, i) => <LoadingRow key={i} />)
                : users.map((user, i) => (
                    <Row
                      key={user.uid}
                      user={user}
                      currentUser={currentUser}
                      onEmailChange={() => handleEmailChange(user, i)}
                      onNicknameChange={() => handleNicknameChange(user, i)}
                      onScoreChange={() => handleScoreChange(user, i)}
                      onPermissionChange={() => handlePermissionChange(user, i)}
                      onVerificationToggle={() =>
                        handleVerificationToggle(user, i)
                      }
                      onPasswordChange={() => handlePasswordChange(user)}
                      onDelete={() => handleDelete(user)}
                    />
                  ))}
            </tbody>
          </table>
        </div>
      ) : (
        <div className="card-body d-flex flex-wrap">
          {isLoading
            ? new Array(10).fill(null).map((_, i) => <LoadingCard key={i} />)
            : users.map((user, i) => (
                <Card
                  key={user.uid}
                  user={user}
                  currentUser={currentUser}
                  onEmailChange={() => handleEmailChange(user, i)}
                  onNicknameChange={() => handleNicknameChange(user, i)}
                  onScoreChange={() => handleScoreChange(user, i)}
                  onPermissionChange={() => handlePermissionChange(user, i)}
                  onVerificationToggle={() => handleVerificationToggle(user, i)}
                  onPasswordChange={() => handlePasswordChange(user)}
                  onDelete={() => handleDelete(user)}
                />
              ))}
        </div>
      )}
      <div className="card-footer">
        <div className="float-right">
          <Pagination page={page} totalPages={totalPages} onChange={setPage} />
        </div>
      </div>
    </div>
  )
}

export default hot(UsersManagement)
