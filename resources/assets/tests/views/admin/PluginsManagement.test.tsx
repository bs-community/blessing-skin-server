import React from 'react'
import { render, wait, fireEvent } from '@testing-library/react'
import { trans } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import PluginsManagement from '@/views/admin/PluginsManagement'

jest.mock('@/scripts/net')
jest.mock('@/scripts/notify')

test('show loading indicator', () => {
  fetch.get.mockResolvedValue([])
  const { queryByTitle } = render(<PluginsManagement />)
  expect(queryByTitle('Loading...')).not.toBeNull()
})

test('plugin info box', async () => {
  fetch.get.mockResolvedValue([
    {
      name: 'a',
      title: 'My Plugin',
      version: '1.0.0',
      description: 'desc',
      config: true,
      readme: true,
      icon: {},
      enabled: true,
    },
  ])

  const { queryByTitle, queryByText } = render(<PluginsManagement />)
  await wait()

  expect(queryByTitle(trans('admin.configurePlugin'))).not.toBeNull()
  expect(queryByTitle(trans('admin.pluginReadme'))).not.toBeNull()
  expect(queryByText('My Plugin')).not.toBeNull()
  expect(queryByText('v1.0.0')).not.toBeNull()
  expect(queryByText('desc')).not.toBeNull()
})

describe('enable plugin', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([
      {
        name: 'a',
        icon: {},
        enabled: false,
      },
    ])
  })

  it('successfully', async () => {
    fetch.get.mockResolvedValue([
      {
        name: 'a',
        icon: {},
        enabled: false,
      },
    ])
    fetch.post.mockResolvedValue({ code: 0, message: '0' })

    const { getByTitle } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.enablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'enable',
      name: 'a',
    })
    expect(toast.success).toBeCalled()
    expect(getByTitle(trans('admin.disablePlugin'))).toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: '1',
      data: { reason: ['abc'] },
    })

    const { getByTitle } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.enablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'enable',
      name: 'a',
    })
    expect(showModal).toBeCalledWith({
      mode: 'alert',
      dangerousHTML: expect.stringContaining('<li>abc</li>'),
    })
  })
})

describe('disable plugin', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([
      {
        name: 'a',
        icon: {},
        enabled: true,
      },
    ])
  })

  it('successfully', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: '0' })

    const { getByTitle } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.disablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'disable',
      name: 'a',
    })
    expect(toast.success).toBeCalledWith('0')
    expect(getByTitle(trans('admin.enablePlugin'))).not.toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: '1' })

    const { getByTitle } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.disablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'disable',
      name: 'a',
    })
    expect(toast.error).toBeCalledWith('1')
  })
})

describe('delete plugin', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([
      {
        name: 'a',
        title: 'My Plugin',
        icon: {},
        enabled: false,
      },
    ])
  })

  it('rejected by user', async () => {
    showModal.mockRejectedValue({})

    const { getByTitle } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    await wait()
    expect(showModal).toBeCalledWith({
      title: 'My Plugin',
      text: trans('admin.confirmDeletion'),
      okButtonType: 'danger',
    })
    expect(fetch.post).not.toBeCalled()
  })

  it('successfully', async () => {
    showModal.mockResolvedValue({ value: '' })
    fetch.post.mockResolvedValue({ code: 0, message: '0' })

    const { getByTitle, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    await wait()
    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'a',
    })
    expect(toast.success).toBeCalledWith('0')
    expect(queryByText('My Plugin')).toBeNull()
  })

  it('failed', async () => {
    showModal.mockResolvedValue({ value: '' })
    fetch.post.mockResolvedValue({ code: 1, message: '1' })

    const { getByTitle, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    await wait()
    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'a',
    })
    expect(toast.error).toBeCalledWith('1')
    expect(queryByText('My Plugin')).not.toBeNull()
  })
})
