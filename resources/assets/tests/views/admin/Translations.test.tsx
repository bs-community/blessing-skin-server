import React from 'react'
import { render, waitFor, fireEvent } from '@testing-library/react'
import { createPaginator } from '../../utils'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import Translations from '@/views/admin/Translations'
import type { Line } from '@/views/admin/Translations/types'

jest.mock('@/scripts/net')

const fixtureLine: Readonly<Line> = Object.freeze<Line>({
  id: 1,
  group: 'general',
  key: 'submit',
  text: {
    en: 'Submit',
  },
})

test('empty text', async () => {
  const line = { ...fixtureLine, text: { en: '' } }
  fetch.get.mockResolvedValue(createPaginator([line]))

  const { queryByText } = render(<Translations />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
  expect(queryByText(t('admin.i18n.empty'))).toBeInTheDocument()
})

describe('edit line', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixtureLine]))
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Translations />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('admin.i18n.modify')))
    fireEvent.input(getByDisplayValue(fixtureLine.text.en!), {
      target: { value: 'finish' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(`/admin/i18n/${fixtureLine.id}`, {
        text: 'finish',
      }),
    )
    expect(queryByText('finish')).toBeInTheDocument()
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Translations />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('admin.i18n.modify')))
    fireEvent.input(getByDisplayValue(fixtureLine.text.en!), {
      target: { value: 'finish' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(`/admin/i18n/${fixtureLine.id}`, {
        text: 'finish',
      }),
    )
    expect(queryByText(fixtureLine.text.en!)).toBeInTheDocument()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('cancelled', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(
      <Translations />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('admin.i18n.modify')))
    fireEvent.input(getByDisplayValue(fixtureLine.text.en!), {
      target: { value: 'finish' },
    })
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.put).not.toBeCalled())
    expect(queryByText(fixtureLine.text.en!)).toBeInTheDocument()
  })
})

describe('delete line', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([fixtureLine]))
  })

  it('succeeded', async () => {
    fetch.del.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Translations />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('admin.i18n.delete')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(`/admin/i18n/${fixtureLine.id}`),
    )
    expect(queryByText(fixtureLine.text.en!)).not.toBeInTheDocument()
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<Translations />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('admin.i18n.delete')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.del).not.toBeCalled())
    expect(queryByText(fixtureLine.text.en!)).toBeInTheDocument()
  })
})
