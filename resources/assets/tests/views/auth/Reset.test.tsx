import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import Reset from '@/views/auth/Reset'

jest.mock('@/scripts/net')

test('confirmation is not matched', () => {
  const { getByText, getByPlaceholderText, queryByText } = render(<Reset />)

  fireEvent.input(getByPlaceholderText(t('auth.password')), {
    target: { value: 'password' },
  })
  fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
    target: { value: 'password1' },
  })
  fireEvent.click(getByText(t('auth.reset-button')))

  expect(queryByText(t('auth.invalidConfirmPwd'))).toBeInTheDocument()
  expect(fetch.post).not.toBeCalled()
})

test('succeeded', async () => {
  fetch.post.mockResolvedValue({ code: 0, message: 'ok' })
  const { getByText, getByPlaceholderText, getByRole, queryByText } = render(
    <Reset />,
  )

  fireEvent.input(getByPlaceholderText(t('auth.password')), {
    target: { value: 'password' },
  })
  fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
    target: { value: 'password' },
  })
  fireEvent.click(getByText(t('auth.reset-button')))
  await waitFor(() =>
    expect(fetch.post).toBeCalledWith(
      location.href.replace(blessing.base_url, ''),
      { password: 'password' },
    ),
  )
  expect(queryByText('ok')).toBeInTheDocument()
  expect(getByRole('status')).toHaveClass('alert-success')
  jest.runAllTimers()
})

test('failed', async () => {
  fetch.post.mockResolvedValue({ code: 1, message: 'failed' })
  const { getByText, getByPlaceholderText, queryByText } = render(<Reset />)

  fireEvent.input(getByPlaceholderText(t('auth.password')), {
    target: { value: 'password' },
  })
  fireEvent.input(getByPlaceholderText(t('auth.repeat-pwd')), {
    target: { value: 'password' },
  })
  fireEvent.click(getByText(t('auth.reset-button')))
  await waitFor(() =>
    expect(fetch.post).toBeCalledWith(
      location.href.replace(blessing.base_url, ''),
      { password: 'password' },
    ),
  )
  expect(queryByText('failed')).toBeInTheDocument()
})
