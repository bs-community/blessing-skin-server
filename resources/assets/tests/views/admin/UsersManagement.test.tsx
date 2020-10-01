import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { createPaginator } from '../../utils'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { User, UserPermission } from '@/scripts/types'
import urls from '@/scripts/urls'
import UsersManagement from '@/views/admin/UsersManagement'

jest.mock('@/scripts/net')

const fixture: Readonly<User> = Object.freeze<User>({
  uid: 1,
  email: 'a@b.c',
  nickname: 'abc',
  locale: 'en',
  score: 1000,
  avatar: 0,
  permission: UserPermission.Normal,
  ip: '::1',
  last_sign_at: new Date().toString(),
  register_at: new Date().toString(),
  verified: true,
})

beforeAll(() => {
  Object.assign(window, { innerWidth: 500 })
})

afterAll(() => {
  Object.assign(window, { innerWidth: 1024 })
})

beforeEach(() => {
  window.blessing.extra = {
    currentUser: { ...fixture, uid: 2, permission: UserPermission.Admin },
  }
})

test('search users', async () => {
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByTitle, getByText } = render(<UsersManagement />)
  await waitFor(() =>
    expect(fetch.get).toBeCalledWith(urls.admin.users.list(), {
      q: '',
      page: 1,
    }),
  )

  fireEvent.input(getByTitle(t('vendor.datatable.search')), {
    target: { value: 's' },
  })
  fireEvent.click(getByText(t('vendor.datatable.search')))
  await waitFor(() =>
    expect(fetch.get).toBeCalledWith(urls.admin.users.list(), {
      q: 's',
      page: 1,
    }),
  )
})

describe('access control', () => {
  describe('current user is super administrator', () => {
    beforeEach(() => {
      window.blessing.extra = {
        currentUser: {
          ...fixture,
          uid: 2,
          permission: UserPermission.SuperAdmin,
        },
      }
    })

    it('target user is super administrator', async () => {
      fetch.get.mockResolvedValue(
        createPaginator([
          { ...fixture, permission: UserPermission.SuperAdmin },
        ]),
      )

      const { getByTitle, getByText, queryByText, queryByTitle } = render(
        <UsersManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())
      expect(queryByText(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePassword'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.toggleVerification'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.deleteUser'))).not.toBeInTheDocument()

      fireEvent.click(getByTitle('Table Mode'))
      expect(queryByTitle(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(
        queryByTitle(t('admin.toggleVerification')),
      ).not.toBeInTheDocument()
      expect(getByText(t('admin.changePassword'))).toBeDisabled()
      expect(getByText(t('admin.deleteUser'))).toBeDisabled()
    })

    it('target user is normal administrator', async () => {
      fetch.get.mockResolvedValue(
        createPaginator([{ ...fixture, permission: UserPermission.Admin }]),
      )

      const { getByTitle, getByText, queryByText, queryByTitle } = render(
        <UsersManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())
      expect(queryByText(t('admin.changeEmail'))).toBeInTheDocument()
      expect(queryByText(t('admin.changeNickName'))).toBeInTheDocument()
      expect(queryByText(t('admin.changePassword'))).toBeInTheDocument()
      expect(queryByText(t('admin.changeScore'))).toBeInTheDocument()
      expect(queryByText(t('admin.changePermission'))).toBeInTheDocument()
      expect(queryByText(t('admin.toggleVerification'))).toBeInTheDocument()
      expect(queryByText(t('admin.deleteUser'))).toBeInTheDocument()

      fireEvent.click(getByTitle('Table Mode'))
      expect(queryByTitle(t('admin.changeEmail'))).toBeInTheDocument()
      expect(queryByTitle(t('admin.changeNickName'))).toBeInTheDocument()
      expect(queryByTitle(t('admin.changeScore'))).toBeInTheDocument()
      expect(queryByTitle(t('admin.changePermission'))).toBeInTheDocument()
      expect(queryByTitle(t('admin.toggleVerification'))).toBeInTheDocument()
      expect(getByText(t('admin.changePassword'))).toBeEnabled()
      expect(getByText(t('admin.deleteUser'))).toBeEnabled()
    })
  })

  describe('current user is normal administrator', () => {
    beforeEach(() => {
      window.blessing.extra = {
        currentUser: {
          ...fixture,
          uid: 2,
          permission: UserPermission.Admin,
        },
      }
    })

    it('target user is super administrator', async () => {
      fetch.get.mockResolvedValue(
        createPaginator([
          { ...fixture, permission: UserPermission.SuperAdmin },
        ]),
      )

      const { getByTitle, getByText, queryByText, queryByTitle } = render(
        <UsersManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())
      expect(queryByText(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePassword'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.toggleVerification'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.deleteUser'))).not.toBeInTheDocument()

      fireEvent.click(getByTitle('Table Mode'))
      expect(queryByTitle(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(
        queryByTitle(t('admin.toggleVerification')),
      ).not.toBeInTheDocument()
      expect(getByText(t('admin.changePassword'))).toBeDisabled()
      expect(getByText(t('admin.deleteUser'))).toBeDisabled()
    })

    it('target user is normal administrator', async () => {
      fetch.get.mockResolvedValue(
        createPaginator([{ ...fixture, permission: UserPermission.Admin }]),
      )

      const { getByTitle, getByText, queryByText, queryByTitle } = render(
        <UsersManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())
      expect(queryByText(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePassword'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.toggleVerification'))).not.toBeInTheDocument()
      expect(queryByText(t('admin.deleteUser'))).not.toBeInTheDocument()

      fireEvent.click(getByTitle('Table Mode'))
      expect(queryByTitle(t('admin.changeEmail'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeNickName'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changeScore'))).not.toBeInTheDocument()
      expect(queryByTitle(t('admin.changePermission'))).not.toBeInTheDocument()
      expect(
        queryByTitle(t('admin.toggleVerification')),
      ).not.toBeInTheDocument()
      expect(getByText(t('admin.changePassword'))).toBeDisabled()
      expect(getByText(t('admin.deleteUser'))).toBeDisabled()
    })
  })

  it('current user and target user are self', async () => {
    const user = {
      ...fixture,
      permission: UserPermission.Admin,
    }
    window.blessing.extra = { currentUser: user }
    fetch.get.mockResolvedValue(createPaginator([user]))

    const { getByTitle, getByText, queryByText, queryByTitle } = render(
      <UsersManagement />,
    )
    await waitFor(() => expect(fetch.get).toBeCalled())
    expect(queryByText(t('admin.changeEmail'))).toBeInTheDocument()
    expect(queryByText(t('admin.changeNickName'))).toBeInTheDocument()
    expect(queryByText(t('admin.changePassword'))).toBeInTheDocument()
    expect(queryByText(t('admin.changeScore'))).toBeInTheDocument()
    expect(queryByText(t('admin.changePermission'))).not.toBeInTheDocument()
    expect(queryByText(t('admin.toggleVerification'))).toBeInTheDocument()
    expect(queryByText(t('admin.deleteUser'))).not.toBeInTheDocument()

    fireEvent.click(getByTitle('Table Mode'))
    expect(queryByTitle(t('admin.changeEmail'))).toBeInTheDocument()
    expect(queryByTitle(t('admin.changeNickName'))).toBeInTheDocument()
    expect(queryByTitle(t('admin.changeScore'))).toBeInTheDocument()
    expect(queryByTitle(t('admin.changePermission'))).not.toBeInTheDocument()
    expect(queryByTitle(t('admin.toggleVerification'))).toBeInTheDocument()
    expect(getByText(t('admin.changePassword'))).toBeEnabled()
    expect(getByText(t('admin.deleteUser'))).toBeDisabled()
  })
})

describe('update email', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('empty value', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeEmail')))
    fireEvent.input(getByDisplayValue(fixture.email), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    expect(queryByText(t('auth.emptyEmail'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeEmail')))
    fireEvent.input(getByDisplayValue(fixture.email), {
      target: { value: 'd@e.f' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.email(fixture.uid), {
        email: 'd@e.f',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText('d@e.f')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeEmail')))
    fireEvent.input(getByDisplayValue(fixture.email), {
      target: { value: 'd@e.f' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.email(fixture.uid), {
        email: 'd@e.f',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })
})

describe('update nickname', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('empty value', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeNickName')))
    fireEvent.input(getByDisplayValue(fixture.nickname), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    expect(queryByText(t('auth.emptyNickname'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.nickname)).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeNickName')))
    fireEvent.input(getByDisplayValue(fixture.nickname), {
      target: { value: 'kumiko' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.nickname(fixture.uid), {
        nickname: 'kumiko',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText('kumiko')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeNickName')))
    fireEvent.input(getByDisplayValue(fixture.nickname), {
      target: { value: 'kumiko' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.nickname(fixture.uid), {
        nickname: 'kumiko',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(fixture.nickname)).toBeInTheDocument()
  })
})

describe('update score', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeScore')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.score.toString())).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeScore')))
    fireEvent.input(getByDisplayValue(fixture.score.toString()), {
      target: { value: '999' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.score(fixture.uid), {
        score: 999,
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText('999')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changeScore')))
    fireEvent.input(getByDisplayValue(fixture.score.toString()), {
      target: { value: '999' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.score(fixture.uid), {
        score: 999,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(fixture.score.toString())).toBeInTheDocument()
  })
})

describe('update permission', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePermission')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(t('admin.normal'))).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByLabelText, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePermission')))
    fireEvent.click(getByLabelText(t('admin.banned')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(
        urls.admin.users.permission(fixture.uid),
        {
          permission: UserPermission.Banned,
        },
      ),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('admin.banned'))).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByLabelText, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePermission')))
    fireEvent.click(getByLabelText(t('admin.banned')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(
        urls.admin.users.permission(fixture.uid),
        {
          permission: UserPermission.Banned,
        },
      ),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(t('admin.normal'))).toBeInTheDocument()
  })

  it('set as administrator', async () => {
    window.blessing.extra = {
      currentUser: {
        ...fixture,
        uid: 2,
        permission: UserPermission.SuperAdmin,
      },
    }
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByLabelText, queryByText, queryByRole } = render(
      <UsersManagement />,
    )

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePermission')))
    fireEvent.click(getByLabelText(t('admin.admin')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(
        urls.admin.users.permission(fixture.uid),
        {
          permission: UserPermission.Admin,
        },
      ),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('admin.admin'))).toBeInTheDocument()
  })
})

describe('toggle verification', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByText, queryByRole } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.toggleVerification')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(
        urls.admin.users.verification(fixture.uid),
      ),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('admin.unverified'))).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, queryByText, queryByRole } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.toggleVerification')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(
        urls.admin.users.verification(fixture.uid),
      ),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(t('admin.verified'))).toBeInTheDocument()
  })
})

describe('update password', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePassword')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.score.toString())).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const {
      getByText,
      getByPlaceholderText,
      queryByText,
      queryByRole,
    } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePassword')))
    fireEvent.input(getByPlaceholderText(t('admin.changePassword')), {
      target: { value: '123' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.password(fixture.uid), {
        password: '123',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const {
      getByText,
      getByPlaceholderText,
      queryByText,
      queryByRole,
    } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.changePassword')))
    fireEvent.input(getByPlaceholderText(t('admin.changePassword')), {
      target: { value: '123' },
    })
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.admin.users.password(fixture.uid), {
        password: '123',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
  })
})

describe('delete user', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.deleteUser')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.del).not.toBeCalled()
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.del.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByText, queryByRole } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.deleteUser')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(urls.admin.users.delete(fixture.uid)),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText(fixture.email)).not.toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.del.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, queryByText, queryByRole } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByText(t('admin.deleteUser')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(urls.admin.users.delete(fixture.uid)),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })
})

describe('table mode', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  it('large screen', async () => {
    Object.assign(window, { innerWidth: 1024 })

    const { queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    expect(queryByText(t('admin.operationsTitle'))).toBeInTheDocument()

    Object.assign(window, { innerWidth: 500 })
  })

  it('update email', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByTitle(t('admin.changeEmail')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })

  it('update nickname', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByTitle(t('admin.changeNickName')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.nickname)).toBeInTheDocument()
  })

  it('update score', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByTitle(t('admin.changeScore')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.score.toString())).toBeInTheDocument()
  })

  it('update permission', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByTitle(t('admin.changePermission')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(t('admin.normal'))).toBeInTheDocument()
  })

  it('toggle verification', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByTitle, queryByText, queryByRole } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByTitle(t('admin.toggleVerification')))

    await waitFor(() => expect(fetch.put).toBeCalled())
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('admin.unverified'))).toBeInTheDocument()
  })

  it('update password', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByText(t('admin.changePassword')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.put).not.toBeCalled()
    expect(queryByText(fixture.score.toString())).toBeInTheDocument()
  })

  it('delete user', async () => {
    const { getByText, getByTitle, queryByText } = render(<UsersManagement />)

    await waitFor(() => expect(fetch.get).toBeCalled())
    fireEvent.click(getByTitle('Table Mode'))
    fireEvent.click(getByText(t('admin.deleteUser')))
    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.del).not.toBeCalled()
    expect(queryByText(fixture.email)).toBeInTheDocument()
  })
})
