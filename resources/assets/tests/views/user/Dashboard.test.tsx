import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import * as fetch from '@/scripts/net'
import { t } from '@/scripts/i18n'
import Dashboard from '@/views/user/Dashboard'

jest.mock('@/scripts/net')

function scoreInfo(data = {}, user = {}, stats = {}) {
  return {
    data: {
      user: { score: 600, lastSignAt: '2018-08-07 16:06:49', ...user },
      stats: {
        players: { used: 3, total: 15 },
        storage: { used: 5, total: 20 },
        ...stats,
      },
      signAfterZero: false,
      signGapTime: '24',
      ...data,
    },
  }
}

describe('info box', () => {
  it('players', async () => {
    fetch.get.mockResolvedValue(
      scoreInfo({}, { score: 0 }, { players: { used: 13, total: 21 } }),
    )

    const { getByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
    expect(getByText('13')).toBeInTheDocument()
    expect(getByText(/21/)).toBeInTheDocument()
  })

  describe('storage', () => {
    it('in KB', async () => {
      fetch.get.mockResolvedValue(
        scoreInfo({}, { score: 0 }, { storage: { used: 700, total: 800 } }),
      )

      const { getByText } = render(<Dashboard />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
      expect(getByText('700')).toBeInTheDocument()
      expect(getByText(/800/)).toBeInTheDocument()
      expect(getByText(/KB/)).toBeInTheDocument()
    })

    it('in MB', async () => {
      fetch.get.mockResolvedValue(
        scoreInfo({}, { score: 0 }, { storage: { used: 7168, total: 10240 } }),
      )

      const { getByText } = render(<Dashboard />)
      await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
      expect(getByText('7')).toBeInTheDocument()
      expect(getByText(/10/)).toBeInTheDocument()
      expect(getByText(/MB/)).toBeInTheDocument()
    })
  })
})

describe('sign', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(scoreInfo())
  })

  it('should succeed', async () => {
    fetch.post.mockResolvedValue({
      code: 0,
      message: 'ok',
      data: { score: 900, storage: { used: 5, total: 25 } },
    })

    const { getByRole, getByText, queryByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    const button = getByRole('button')
    fireEvent.click(button)
    await waitFor(() => expect(fetch.post).toBeCalledWith('/user/sign'))
    expect(getByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(button).toBeDisabled()
    expect(queryByText(/25/)).toBeInTheDocument()
  })

  it('should fail', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'f', data: {} })

    const { getByRole, getByText } = render(<Dashboard />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByRole('button'))
    await waitFor(() => expect(fetch.post).toBeCalledWith('/user/sign'))
    expect(getByText('f')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-warning')
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
