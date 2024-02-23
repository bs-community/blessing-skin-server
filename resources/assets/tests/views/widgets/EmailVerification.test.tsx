import { expect, vi, it, describe } from 'vitest'
import { render, fireEvent, waitFor } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import EmailVerification from '@/views/widgets/EmailVerification'

vi.mock('@/scripts/net')

describe('send email', () => {
  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByRole, queryByText } = render(<EmailVerification />)

    fireEvent.click(getByText(t('user.verification.resend')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/email-verification'),
    )
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<EmailVerification />)

    fireEvent.click(getByText(t('user.verification.resend')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/email-verification'),
    )
    expect(fetch.post).toBeCalledWith('/user/email-verification')
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})
