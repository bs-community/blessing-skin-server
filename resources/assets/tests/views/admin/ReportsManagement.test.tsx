import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { createPaginator } from '../../utils'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { Texture, TextureType, User, UserPermission } from '@/scripts/types'
import ReportsManagement from '@/views/admin/ReportsManagement'
import { Report, Status } from '@/views/admin/ReportsManagement/types'

jest.mock('@/scripts/net')

const fixture: Readonly<Report> = Object.freeze<Report>({
  id: 1,
  tid: 1,
  texture: null,
  uploader: 1,
  texture_uploader: Object.freeze<Readonly<User>>({
    uid: 1,
    email: 'a@b.c',
    nickname: 'abc',
    locale: 'en',
    score: 1000,
    avatar: 0,
    permission: UserPermission.Normal,
    ip: '::1',
    is_dark_mode: false,
    last_sign_at: new Date().toString(),
    register_at: new Date().toString(),
    verified: true,
  }),
  reporter: 2,
  informer: Object.freeze<Readonly<User>>({
    uid: 1,
    email: 'a@b.c',
    nickname: 'abc',
    locale: 'en',
    score: 1000,
    avatar: 0,
    permission: UserPermission.Normal,
    ip: '::1',
    is_dark_mode: false,
    last_sign_at: new Date().toString(),
    register_at: new Date().toString(),
    verified: true,
  }),
  reason: 'nsfw',
  status: Status.Pending,
  report_at: new Date().toString(),
})

test('search reports', async () => {
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByTitle, getByText } = render(<ReportsManagement />)
  await waitFor(() =>
    expect(fetch.get).toBeCalledWith('/admin/reports/list', {
      q: 'status:0 sort:-report_at',
      page: 1,
    }),
  )

  fireEvent.input(getByTitle(t('vendor.datatable.search')), {
    target: { value: 's' },
  })
  fireEvent.click(getByText(t('vendor.datatable.search')))
  await waitFor(() =>
    expect(fetch.get).toBeCalledWith('/admin/reports/list', {
      q: 's',
      page: 1,
    }),
  )
})

test('empty reporter or texture uploader', () => {
  const report = { ...fixture, texture_uploader: null, informer: null }
  fetch.get.mockResolvedValue(createPaginator([report]))

  render(<ReportsManagement />)
})

test('preview texture', async () => {
  const texture: Texture = {
    tid: fixture.tid,
    name: 'cape',
    type: TextureType.Cape,
    hash: 'def',
    size: 2,
    uploader: fixture.uploader,
    public: true,
    upload_at: new Date().toString(),
    likes: 1,
  }
  fetch.get.mockResolvedValue(createPaginator([{ ...fixture, texture }]))

  const { getByAltText } = render(<ReportsManagement />)
  await waitFor(() => expect(fetch.get).toBeCalled())

  fireEvent.click(getByAltText(fixture.tid.toString()))
})

describe('proceed report', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixture]))
  })

  describe('ban uploader', () => {
    it('succeeded', async () => {
      fetch.put.mockResolvedValue({
        code: 0,
        message: 'ok',
        data: { status: Status.Resolved },
      })

      const { getByText, getByRole, queryByText } = render(
        <ReportsManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())

      fireEvent.click(getByText(t('report.ban')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/admin/reports/${fixture.id}`, {
          action: 'ban',
        }),
      )
      expect(queryByText('ok')).toBeInTheDocument()
      expect(getByRole('status')).toBeInTheDocument()
      expect(queryByText(t('report.status.1'))).toBeInTheDocument()
    })

    it('failed', async () => {
      fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

      const { getByText, getByRole, queryByText } = render(
        <ReportsManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())

      fireEvent.click(getByText(t('report.ban')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/admin/reports/${fixture.id}`, {
          action: 'ban',
        }),
      )
      expect(queryByText('failed')).toBeInTheDocument()
      expect(getByRole('alert')).toBeInTheDocument()
      expect(queryByText(t('report.status.0'))).toBeInTheDocument()
    })
  })

  describe('delete texture', () => {
    it('cancelled', async () => {
      const { getByText } = render(<ReportsManagement />)
      await waitFor(() => expect(fetch.get).toBeCalled())

      fireEvent.click(getByText(t('skinlib.show.delete-texture')))
      fireEvent.click(getByText(t('general.cancel')))
      expect(fetch.put).not.toBeCalled()
    })

    it('succeeded', async () => {
      fetch.put.mockResolvedValue({
        code: 0,
        message: 'ok',
        data: { status: Status.Resolved },
      })

      const { getByText, getByRole, queryByText } = render(
        <ReportsManagement />,
      )
      await waitFor(() => expect(fetch.get).toBeCalled())

      fireEvent.click(getByText(t('skinlib.show.delete-texture')))
      fireEvent.click(getByText(t('general.confirm')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/admin/reports/${fixture.id}`, {
          action: 'delete',
        }),
      )
      expect(queryByText('ok')).toBeInTheDocument()
      expect(getByRole('status')).toBeInTheDocument()
      expect(queryByText(t('report.status.1'))).toBeInTheDocument()
    })
  })

  it('reject report', async () => {
    fetch.put.mockResolvedValue({
      code: 0,
      message: 'ok',
      data: { status: Status.Rejected },
    })

    const { getByText, getByRole, queryByText } = render(<ReportsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('report.reject')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(`/admin/reports/${fixture.id}`, {
        action: 'reject',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toBeInTheDocument()
    expect(queryByText(t('report.status.2'))).toBeInTheDocument()
  })
})
