import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import * as fetch from '@/scripts/net'
import { t } from '@/scripts/i18n'
import urls from '@/scripts/urls'
import Dashboard from '@/views/user/Dashboard'
import * as scoreUtils from '@/views/user/Dashboard/scoreUtils'

jest.mock('@/scripts/net')

function scoreInfo(data = {}, user = {}, usage = {}) {
  return {
    user: { score: 600, lastSignAt: '2018-08-07 16:06:49', ...user },
    usage: {
      players: 3,
      storage: 5,
      ...usage,
    },
    rate: {
      players: 10,
      storage: 1,
    },
    signAfterZero: false,
    signGapTime: '24',
    ...data,
  }
}

describe('info box', () => {
  it('players', async () => {
    fetch.get.mockResolvedValue(scoreInfo({}, { score: 40 }))

    const { getByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    expect(getByText('3')).toBeInTheDocument()
    expect(getByText(/7/)).toBeInTheDocument()
  })

  describe('storage', () => {
    it('in KB', async () => {
      fetch.get.mockResolvedValue(
        scoreInfo({}, { score: 100 }, { storage: 700 }),
      )

      const { getByText } = render(<Dashboard />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
      expect(getByText('700')).toBeInTheDocument()
      expect(getByText(/800/)).toBeInTheDocument()
      expect(getByText(/KB/)).toBeInTheDocument()
    })

    it('in MB', async () => {
      fetch.get.mockResolvedValue(
        scoreInfo({}, { score: 3072 }, { storage: 4096 }),
      )

      const { getByText } = render(<Dashboard />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
      expect(getByText('4')).toBeInTheDocument()
      expect(getByText(/7/)).toBeInTheDocument()
      expect(getByText(/MB/)).toBeInTheDocument()
    })
  })
})

describe('sign', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(scoreInfo())
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({
      code: 0,
      message: 'ok',
      data: { score: 900 },
    })

    const { getByRole, getByText, queryByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    const button = getByRole('button')
    fireEvent.click(button)
    await waitFor(() => expect(fetch.post).toBeCalledWith(urls.user.sign()))
    expect(getByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(button).toBeDisabled()
    expect(queryByText(/905/)).toBeInTheDocument()
  })

  it('cannot sign', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: '' })

    const { getByRole, getByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByRole('button'))
    await waitFor(() => expect(fetch.post).toBeCalledWith(urls.user.sign()))

    expect(getByText(/2[34]\sh/)).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-warning')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 2, message: 'f' })

    const { getByRole, getByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByRole('button'))
    await waitFor(() => expect(fetch.post).toBeCalledWith(urls.user.sign()))

    expect(getByText('f')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})

describe('sign button', () => {
  it('should disabled when loading', () => {
    fetch.get.mockResolvedValue(scoreInfo())
    const { getByRole } = render(<Dashboard />)
    expect(getByRole('button')).toBeDisabled()
  })

  it('sign is allowed', async () => {
    fetch.get.mockResolvedValue(scoreInfo())
    const { getByRole } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    const button = getByRole('button')

    expect(button).toBeEnabled()
    expect(button).toHaveTextContent(t('user.sign'))
  })

  it('sign is allowed if last sign is yesterday', async () => {
    fetch.get.mockResolvedValue(scoreInfo({ signAfterZero: true }))
    const { getByRole } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    expect(getByRole('button')).toBeEnabled()
  })

  it('sign is not allowed', async () => {
    fetch.get.mockResolvedValue(
      scoreInfo({ signAfterZero: true }, { lastSignAt: Date.now() }),
    )
    const { getByRole } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    expect(getByRole('button')).toBeDisabled()
  })

  it('remain in hours', async () => {
    jest.useRealTimers()
    fetch.get.mockResolvedValue(scoreInfo({}, { lastSignAt: Date.now() }))

    const { getByRole } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    const button = getByRole('button')

    expect(button).toBeDisabled()
    expect(button).toHaveTextContent('23 h')
  })

  it('remain in minutes', async () => {
    fetch.get.mockResolvedValue(
      scoreInfo({}, { lastSignAt: Date.now() - 23.5 * 3600 * 1000 }),
    )

    const { getByRole } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    const button = getByRole('button')

    expect(button).toBeDisabled()
    expect(button).toHaveTextContent(/(29|30)\smin/)
  })
})
