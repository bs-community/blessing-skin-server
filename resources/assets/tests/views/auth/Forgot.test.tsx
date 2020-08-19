import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import urls from '@/scripts/urls'
import Forgot from '@/views/auth/Forgot'

jest.mock('@/scripts/net')

describe('submit', () => {
  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByPlaceholderText, getByText, queryByText } = render(<Forgot />)

    fireEvent.input(getByPlaceholderText(t('auth.email')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
      target: { value: 'abc' },
    })
    fireEvent.click(getByText(t('auth.send')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.forgot(), {
        email: 'a@b.c',
        captcha: 'abc',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByPlaceholderText, getByText, queryByText } = render(<Forgot />)

    fireEvent.input(getByPlaceholderText(t('auth.email')), {
      target: { value: 'a@b.c' },
    })
    fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
      target: { value: 'abc' },
    })
    fireEvent.click(getByText(t('auth.send')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(urls.auth.forgot(), {
        email: 'a@b.c',
        captcha: 'abc',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
  })
})
