import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
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
    {
      name: 'b',
      title: 'Another Plugin',
      version: '0.1.0',
      description: '',
      config: false,
      readme: false,
      icon: {},
      enabled: false,
    },
  ])

  const { queryByTitle, queryByText } = render(<PluginsManagement />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  expect(queryByTitle(t('admin.configurePlugin'))).not.toBeNull()
  expect(queryByTitle(t('admin.pluginReadme'))).not.toBeNull()
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

  it('succeeded', async () => {
    fetch.get.mockResolvedValue([
      {
        name: 'a',
        icon: {},
        enabled: false,
      },
    ])
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.enablePlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'enable',
        name: 'a',
      }),
    )
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(getByTitle(t('admin.disablePlugin'))).toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'unresolved',
      data: { reason: ['abc'] },
    })

    const { getByTitle, getByText, queryByText } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.enablePlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'enable',
        name: 'a',
      }),
    )
    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'enable',
      name: 'a',
    })
    expect(queryByText('unresolved')).toBeInTheDocument()
    expect(queryByText('abc')).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
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

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.disablePlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'disable',
        name: 'a',
      }),
    )
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(getByTitle(t('admin.enablePlugin'))).not.toBeChecked()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByTitle, getByRole, queryByText } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.disablePlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'disable',
        name: 'a',
      }),
    )
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
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.deletePlugin')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByTitle, getByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.deletePlugin')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'delete',
        name: 'a',
      }),
    )
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('My Plugin')).toBeNull()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByTitle, getByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByTitle(t('admin.deletePlugin')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
        action: 'delete',
        name: 'a',
      }),
    )
    expect(fetch.post).toBeCalledWith('/admin/plugins/manage', {
      action: 'delete',
      name: 'a',
    })
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('My Plugin')).not.toBeNull()
  })
})

describe('upload plugin archive', () => {
  it('no selected file', async () => {
    fetch.get.mockResolvedValue([])

    const { getAllByText } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByText(t('general.submit'))[0]!)
    expect(fetch.post).not.toBeCalled()
  })

  it('succeeded', async () => {
    fetch.get.mockResolvedValue([])
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByTitle, getAllByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    const file = new File([], 'plugin.zip')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getAllByText(t('general.submit'))[0]!)
    await waitFor(() => {
      expect(fetch.get).toBeCalledTimes(2)
      expect(fetch.post).toBeCalledWith(
        '/admin/plugins/upload',
        expect.any(FormData),
      )
    })
    const formData = fetch.post.mock.calls[0]![1] as FormData
    expect(formData.get('file')).toStrictEqual(file)
    expect(queryByText('plugin.zip')).not.toBeInTheDocument()
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.get.mockResolvedValue([])
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByTitle, getAllByText, getByRole, queryByText } = render(
      <PluginsManagement />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    const file = new File([], 'plugin.zip')
    fireEvent.change(getByTitle(t('skinlib.upload.select-file')), {
      target: { files: [file] },
    })
    fireEvent.click(getAllByText(t('general.submit'))[0]!)
    await waitFor(() => {
      expect(fetch.get).toBeCalledTimes(1)
      expect(fetch.post).toBeCalledWith(
        '/admin/plugins/upload',
        expect.any(FormData),
      )
    })
    expect(queryByText('plugin.zip')).toBeInTheDocument()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})

describe('submit remote URL', () => {
  it('succeeded', async () => {
    fetch.get.mockResolvedValue([])
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const {
      getByLabelText,
      getAllByText,
      getByRole,
      queryByText,
      queryByDisplayValue,
    } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.input(getByLabelText('URL'), {
      target: { value: 'https://example.com/a.zip' },
    })
    fireEvent.click(getAllByText(t('general.submit'))[1]!)
    await waitFor(() => {
      expect(fetch.get).toBeCalledTimes(2)
      expect(fetch.post).toBeCalledWith('/admin/plugins/wget', {
        url: 'https://example.com/a.zip',
      })
    })
    expect(
      queryByDisplayValue('https://example.com/a.zip'),
    ).not.toBeInTheDocument()
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.get.mockResolvedValue([])
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const {
      getByLabelText,
      getAllByText,
      getByRole,
      queryByText,
      queryByDisplayValue,
    } = render(<PluginsManagement />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.input(getByLabelText('URL'), {
      target: { value: 'https://example.com/a.zip' },
    })
    fireEvent.click(getAllByText(t('general.submit'))[1]!)
    await waitFor(() => {
      expect(fetch.get).toBeCalledTimes(1)
      expect(fetch.post).toBeCalledWith('/admin/plugins/wget', {
        url: 'https://example.com/a.zip',
      })
    })
    expect(queryByDisplayValue('https://example.com/a.zip')).toBeInTheDocument()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})
