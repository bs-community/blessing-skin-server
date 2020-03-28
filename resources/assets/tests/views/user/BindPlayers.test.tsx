import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import BindPlayers from '@/views/user/BindPlayers'

jest.mock('@/scripts/net')

test('loading indicator', () => {
  fetch.get.mockResolvedValue({ data: [] })
  const { queryByTitle } = render(<BindPlayers />)
  expect(queryByTitle('Loading...')).toBeInTheDocument()
})

describe('submit', () => {
  it('have existed players', async () => {
    fetch.get.mockResolvedValue({
      data: [{ name: 'kumiko' }, { name: 'reina' }],
    })
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByLabelText, queryByText } = render(<BindPlayers />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByLabelText('reina'))
    fireEvent.click(getByText(t('general.submit')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/player/bind', {
        player: 'reina',
      }),
    )
    expect(queryByText('success')).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
  })

  it('no existed players', async () => {
    fetch.get.mockResolvedValue({ data: [] })
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByPlaceholderText, queryByText } = render(
      <BindPlayers />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.input(getByPlaceholderText(t('general.player.player-name')), {
      target: { value: 'kumiko' },
    })
    fireEvent.click(getByText(t('general.submit')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/player/bind', {
        player: 'kumiko',
      }),
    )
    expect(queryByText('success')).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
  })

  it('failed', async () => {
    fetch.get.mockResolvedValue({ data: [] })
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByPlaceholderText, queryByText } = render(
      <BindPlayers />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.input(getByPlaceholderText(t('general.player.player-name')), {
      target: { value: 'kumiko' },
    })
    fireEvent.click(getByText(t('general.submit')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/player/bind', {
        player: 'kumiko',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()

    fireEvent.click(getByText(t('general.confirm')))
  })
})
