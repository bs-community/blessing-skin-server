import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import urls from '@/scripts/urls'
import Login from '@/views/auth/Login'

jest.mock('@/scripts/net')

beforeEach(() => {
  window.blessing.extra = { tooManyFails: false }
})

test('show captcha if too many login fails', () => {
  window.blessing.extra = { tooManyFails: true }
  const { queryByAltText } = render(<Login />)
  expect(queryByAltText(t('auth.captcha'))).toBeInTheDocument()
})

describe('submit form', () => {
  it('succeeded', async () => {
    fetch.post.mockResolvedValue({
      code: 0,
      message: 'ok',
      data: { redirectTo: '/user' },
    })

    const { getByPlaceholderText, getByText } = render(<Login />)
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
      }),
    )
  })

  it('remember me', async () => {
    fetch.post.mockResolvedValue({
      code: 0,
      message: 'ok',
      data: { redirectTo: '/user' },
    })

    const { getByPlaceholderText, getByText, getByLabelText } = render(
      <Login />,
    )
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByLabelText(t('auth.keep')))
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: true,
      }),
    )
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { login_fails: 1 },
    })

    const { getByPlaceholderText, getByText, queryByText } = render(<Login />)
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
  })

  it('too many fails', async () => {
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { login_fails: 4 },
    })

    const { getByPlaceholderText, getByText, queryByText, queryByAltText } =
      render(<Login />)
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByText(t('auth.tooManyFails.captcha'))).toBeInTheDocument()
    expect(queryByAltText(t('auth.captcha'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
    fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
      target: { value: 'captcha' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
        captcha: 'captcha',
      }),
    )
  })

  it('too many fails with normal recaptcha', async () => {
    window.blessing.extra.recaptcha = 'sitekey'
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { login_fails: 4 },
    })

    const { getByPlaceholderText, getByText, queryByText } = render(<Login />)
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
  })

  it('too many fails with invisible recaptcha', async () => {
    window.blessing.extra.recaptcha = 'sitekey'
    window.blessing.extra.invisible = true
    fetch.post.mockResolvedValue({
      code: 1,
      message: 'failed',
      data: { login_fails: 4 },
    })

    const { getByPlaceholderText, getByText, queryByText } = render(<Login />)
    fireEvent.input(getByPlaceholderText(t('auth.identification')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.password')), {
      target: { value: 'password' },
    })
    fireEvent.click(getByText(t('auth.login')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.login(), {
        identification: 'a@b.c',
        password: 'password',
        keep: false,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(queryByText(t('auth.tooManyFails.recaptcha'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
  })
})
