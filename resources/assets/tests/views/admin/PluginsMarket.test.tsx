import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import PluginsMarket from '@/views/admin/PluginsMarket'
import type { Plugin } from '@/views/admin/PluginsMarket/types'

jest.mock('@/scripts/net')

const fixture: Readonly<Plugin> = Object.freeze<Readonly<Plugin>>({
  name: 'yggdrasil-api',
  title: 'Yggdrasil API',
  description: 'Auth System',
  version: '1.0.0',
  author: 'Blessing Skin',
  installed: false,
  dependencies: {
    all: {
      'blessing-skin-server': '^5.0.0',
    },
    unsatisfied: {},
  },
})

test('search plugins', async () => {
  fetch.get.mockResolvedValue([fixture])

  const { getByPlaceholderText, queryByText } = render(<PluginsMarket />)
  await waitFor(() => expect(fetch.get).toBeCalled())

  fireEvent.input(getByPlaceholderText(t('vendor.datatable.search')), {
    target: { value: 'test' },
  })
  expect(queryByText('yggdrasil-api')).not.toBeInTheDocument()
})

test('install a plugin after first page', async () => {
  const plugins = Array.from({ length: 10 }).map(() => {
    return { ...fixture, name: `${fixture.name}_${Math.random()}` }
  })
  plugins.push(fixture)
  fetch.get.mockResolvedValue(plugins)
  fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

  const { getByText, queryByText, queryAllByText } = render(<PluginsMarket />)
  await waitFor(() => expect(fetch.get).toBeCalled())

  fireEvent.click(getByText('2'))
  fireEvent.click(getByText(t('admin.installPlugin')))
  await waitFor(() =>
    expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
      name: fixture.name,
    }),
  )
  expect(queryByText(t('admin.installPlugin'))).toBeDisabled()

  fireEvent.click(getByText('1'))
  expect(queryAllByText(t('admin.installPlugin'))[0]).toBeEnabled()
})

describe('dependencies', () => {
  it('no dependencies', async () => {
    fetch.get.mockResolvedValue([
      { ...fixture, dependencies: { all: {}, unsatisfied: {} } },
    ])

    const { queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())
    expect(queryByText(t('admin.noDependencies'))).toBeInTheDocument()
  })

  it('satisfied dependencies', async () => {
    fetch.get.mockResolvedValue([fixture])

    const { queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())
    expect(
      queryByText(
        `blessing-skin-server: ${fixture.dependencies.all['blessing-skin-server']}`,
      ),
    ).toHaveClass('bg-green')
  })

  it('unsatisfied dependencies', async () => {
    fetch.get.mockResolvedValue([
      {
        ...fixture,
        dependencies: {
          all: { 'blessing-skin-server': '^5.0.0' },
          unsatisfied: { 'blessing-skin-server': '4.0.0' },
        },
      },
    ])

    const { queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())
    expect(
      queryByText(
        `blessing-skin-server: ${fixture.dependencies.all['blessing-skin-server']}`,
      ),
    ).toHaveClass('bg-red')
  })
})

describe('install plugin', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([fixture])
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByRole, queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('admin.installPlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
        name: fixture.name,
      }),
    )
    expect(queryByText(t('admin.installPlugin'))).toBeDisabled()
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('admin.installPlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
        name: fixture.name,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByText(t('admin.installPlugin'))).toBeEnabled()

    fireEvent.click(getByText(t('general.confirm')))
  })

  it('failed with unsatisfied', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { reason: ['version is too low'] },
    })

    const { getByText, queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('admin.installPlugin')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
        name: fixture.name,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByText('version is too low')).toBeInTheDocument()
    expect(queryByText(t('admin.installPlugin'))).toBeEnabled()

    fireEvent.click(getByText(t('general.confirm')))
  })
})

describe('update plugin', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([
      { ...fixture, can_update: true, installed: '0.5.0' },
    ])
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('admin.updatePlugin')))
    expect(
      queryByText(
        t('admin.confirmUpdate', {
          plugin: fixture.title,
          old: '0.5.0',
          new: fixture.version,
        }),
      ),
    ).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.post).not.toBeCalled()
  })

  it('confirm to update', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByText } = render(<PluginsMarket />)
    await waitFor(() => expect(fetch.get).toBeCalled())

    fireEvent.click(getByText(t('admin.updatePlugin')))
    fireEvent.click(getByText(t('general.confirm')))

    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/admin/plugins/market/download', {
        name: fixture.name,
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(queryByText(t('admin.updatePlugin'))).not.toBeInTheDocument()
    expect(queryByText(t('admin.installPlugin'))).toBeDisabled()
  })
})
