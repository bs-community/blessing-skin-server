import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import * as fetch from '@/scripts/net'
import { t } from '@/scripts/i18n'
import OAuth from '@/views/user/OAuth'
import type { App } from '@/views/user/OAuth/types'

jest.mock('@/scripts/net')

const fixture: Readonly<App> = Object.freeze({
  id: 1,
  name: 'My App',
  redirect: 'http://url.test/',
  secret: 'abc',
})

test('loading data', () => {
  fetch.get.mockResolvedValue([])
  const { queryByTitle } = render(<OAuth />)
  expect(queryByTitle('Loading...')).toBeInTheDocument()
})

describe('create app', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([])
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue(fixture)
    const { getByLabelText, getByText, queryByText } = render(<OAuth />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('user.oauth.create')))
    fireEvent.input(getByLabelText(t('user.oauth.name')), {
      target: { value: 'My App' },
    })
    fireEvent.input(getByLabelText(t('user.oauth.redirect')), {
      target: { value: 'http://url.test/' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/oauth/clients', {
        name: 'My App',
        redirect: 'http://url.test/',
      }),
    )
    expect(queryByText(fixture.id.toString())).toBeInTheDocument()
    expect(queryByText(fixture.name)).toBeInTheDocument()
    expect(queryByText(fixture.redirect)).toBeInTheDocument()
    expect(queryByText(fixture.secret)).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ message: 'exception' })
    const { getByLabelText, getByText, getByRole, queryByText } = render(
      <OAuth />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('user.oauth.create')))
    fireEvent.input(getByLabelText(t('user.oauth.name')), {
      target: { value: 'My App' },
    })
    fireEvent.input(getByLabelText(t('user.oauth.redirect')), {
      target: { value: 'http://url.test/' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/oauth/clients', {
        name: 'My App',
        redirect: 'http://url.test/',
      }),
    )
    expect(queryByText(fixture.name)).not.toBeInTheDocument()
    expect(queryByText(fixture.redirect)).not.toBeInTheDocument()
    expect(queryByText('exception')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('cancel dialog', async () => {
    const { getByLabelText, getByText } = render(<OAuth />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('user.oauth.create')))
    fireEvent.input(getByLabelText(t('user.oauth.name')), {
      target: { value: 'My App' },
    })
    fireEvent.input(getByLabelText(t('user.oauth.redirect')), {
      target: { value: 'http://url.test/' },
    })
    fireEvent.click(getByText(t('general.cancel')))

    await waitFor(() => expect(fetch.post).not.toBeCalled())

    fireEvent.click(getByText(t('user.oauth.create')))
    expect(getByLabelText(t('user.oauth.name'))).toHaveValue('')
    expect(getByLabelText(t('user.oauth.redirect'))).toHaveValue('')
  })
})

describe('edit app', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([fixture])
  })

  describe('edit name', () => {
    it('succeeded', async () => {
      fetch.put.mockResolvedValue({ ...fixture, name: 'new name' })

      const { getByTitle, getByText, getByDisplayValue, queryByText } = render(
        <OAuth />,
      )
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyName')))
      fireEvent.input(getByDisplayValue(fixture.name), {
        target: { value: 'new name' },
      })
      fireEvent.click(getByText(t('general.confirm')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/oauth/clients/${fixture.id}`, {
          ...fixture,
          name: 'new name',
        }),
      )
      expect(queryByText('new name')).toBeInTheDocument()
    })

    it('failed', async () => {
      fetch.put.mockResolvedValue({ message: 'exception' })

      const {
        getByTitle,
        getByText,
        getByDisplayValue,
        getByRole,
        queryByText,
      } = render(<OAuth />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyName')))
      fireEvent.input(getByDisplayValue(fixture.name), {
        target: { value: 'new name' },
      })
      fireEvent.click(getByText(t('general.confirm')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/oauth/clients/${fixture.id}`, {
          ...fixture,
          name: 'new name',
        }),
      )
      expect(queryByText(fixture.name)).toBeInTheDocument()
      expect(queryByText('exception')).toBeInTheDocument()
      expect(getByRole('alert')).toHaveClass('alert-danger')
    })

    it('cancel dialog', async () => {
      const { getByTitle, getByText, queryByText } = render(<OAuth />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyName')))
      fireEvent.click(getByText(t('general.cancel')))
      await waitFor(() => expect(fetch.put).not.toBeCalled())
      expect(queryByText(fixture.name)).toBeInTheDocument()
    })
  })

  describe('edit redirect url', () => {
    it('succeeded', async () => {
      fetch.put.mockResolvedValue({ ...fixture, redirect: 'http://new.test/' })

      const { getByTitle, getByDisplayValue, getByText, queryByText } = render(
        <OAuth />,
      )
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyUrl')))
      fireEvent.input(getByDisplayValue(fixture.redirect), {
        target: { value: 'http://new.test/' },
      })
      fireEvent.click(getByText(t('general.confirm')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/oauth/clients/${fixture.id}`, {
          ...fixture,
          redirect: 'http://new.test/',
        }),
      )
      expect(queryByText('http://new.test/')).toBeInTheDocument()
    })

    it('failed', async () => {
      fetch.put.mockResolvedValue({ message: 'exception' })

      const {
        getByTitle,
        getByDisplayValue,
        getByText,
        getByRole,
        queryByText,
      } = render(<OAuth />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyUrl')))
      fireEvent.input(getByDisplayValue(fixture.redirect), {
        target: { value: 'http://new.test/' },
      })
      fireEvent.click(getByText(t('general.confirm')))
      await waitFor(() =>
        expect(fetch.put).toBeCalledWith(`/oauth/clients/${fixture.id}`, {
          ...fixture,
          redirect: 'http://new.test/',
        }),
      )
      expect(queryByText(fixture.redirect)).toBeInTheDocument()
      expect(queryByText('exception')).toBeInTheDocument()
      expect(getByRole('alert')).toHaveClass('alert-danger')
    })

    it('cancel dialog', async () => {
      const { getByTitle, getByText, queryByText } = render(<OAuth />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

      fireEvent.click(getByTitle(t('user.oauth.modifyUrl')))
      fireEvent.click(getByText(t('general.cancel')))
      await waitFor(() => expect(fetch.put).not.toBeCalled())
      expect(queryByText(fixture.redirect)).toBeInTheDocument()
    })
  })
})

describe('delete app', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue([fixture])
  })

  it('succeeded', async () => {
    const { getByText, queryByText } = render(<OAuth />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('report.delete')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(`/oauth/clients/${fixture.id}`),
    )
    expect(queryByText(fixture.name)).not.toBeInTheDocument()
    expect(queryByText(fixture.redirect)).not.toBeInTheDocument()
  })

  it('cancel dialog', async () => {
    const { getByText, queryByText } = render(<OAuth />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('report.delete')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.del).not.toBeCalled())
    expect(queryByText(fixture.name)).toBeInTheDocument()
    expect(queryByText(fixture.redirect)).toBeInTheDocument()
  })
})
