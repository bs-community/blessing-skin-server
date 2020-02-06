import React from 'react'
import { render, wait, fireEvent } from '@testing-library/react'
import { trans } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import PluginsManagement from '@/views/admin/PluginsManagement'

jest.mock('@/scripts/net')

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
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.enablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'enable',
      name: 'a',
    })
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(getByTitle(trans('admin.disablePlugin'))).toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'unresolved',
      data: { reason: ['abc'] },
    })

    const { getByTitle, getByText, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.enablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'enable',
      name: 'a',
    })
    expect(queryByText('unresolved')).toBeInTheDocument()
    expect(queryByText('abc')).toBeInTheDocument()

    fireEvent.click(getByText(trans('general.confirm')))
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
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.disablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'disable',
      name: 'a',
    })
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(getByTitle(trans('admin.enablePlugin'))).not.toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.disablePlugin')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'disable',
      name: 'a',
    })
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
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
    const { getByTitle, getByText } = render(<PluginsManagement />)
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    fireEvent.click(getByText(trans('general.cancel')))
    await wait()

    expect(fetch.post).not.toBeCalled()
  })

  it('successfully', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    fireEvent.click(getByText(trans('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'a',
    })
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('My Plugin')).toBeNull()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByTitle, getByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await wait()

    fireEvent.click(getByTitle(trans('admin.deletePlugin')))
    fireEvent.click(getByText(trans('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'a',
    })
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('My Plugin')).not.toBeNull()
  })
})
