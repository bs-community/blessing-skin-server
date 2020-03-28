import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { Texture } from '@/scripts/types'
import Show, { Badge } from '@/views/skinlib/Show'

jest.mock('@/scripts/net')

const fixtureSkin: Readonly<Texture> = Object.freeze<Texture>({
  tid: 1,
  name: 'skin',
  type: 'steve',
  hash: 'abc',
  size: 2,
  uploader: 1,
  public: true,
  upload_at: new Date().toString(),
  likes: 1,
})

const fixtureCape: Readonly<Texture> = Object.freeze<Texture>({
  tid: 2,
  name: 'cape',
  type: 'cape',
  hash: 'def',
  size: 2,
  uploader: 1,
  public: true,
  upload_at: new Date().toString(),
  likes: 1,
})

beforeEach(() => {
  const container = document.createElement('div')
  container.id = 'previewer'
  document.body.appendChild(container)

  window.blessing.extra = {
    download: true,
    currentUid: 0,
    admin: false,
    nickname: 'author',
    inCloset: false,
    report: 0,
    badges: [],
  }
})

afterEach(() => {
  document.querySelector('#previewer')!.remove()
})

test('without authenticated', async () => {
  fetch.get.mockResolvedValue({ data: fixtureSkin })

  const { queryByText, queryByTitle } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  expect(queryByText(fixtureSkin.name)).toBeInTheDocument()
  expect(queryByText('steve')).toBeInTheDocument()
  expect(queryByText(`${fixtureSkin.size} KB`)).toBeInTheDocument()
  expect(queryByText(fixtureSkin.hash)).toBeInTheDocument()
  expect(queryByText(window.blessing.extra.nickname)).toHaveAttribute(
    'href',
    `/skinlib?filter=skin&uploader=${fixtureSkin.uploader}`,
  )
  expect(queryByTitle(t('skinlib.show.edit'))).not.toBeInTheDocument()
  expect(queryByText(t('skinlib.addToCloset'))).toBeDisabled()
})

test('authenticated but not uploader', async () => {
  fetch.get.mockResolvedValue({ data: fixtureCape })

  const { queryByText, queryByTitle } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  expect(queryByText(fixtureCape.name)).toBeInTheDocument()
  expect(queryByText(t('general.cape'))).toBeInTheDocument()
  expect(queryByText(`${fixtureCape.size} KB`)).toBeInTheDocument()
  expect(queryByText(fixtureCape.hash)).toBeInTheDocument()
  expect(queryByText(window.blessing.extra.nickname)).toHaveAttribute(
    'href',
    `/skinlib?filter=cape&uploader=${fixtureCape.uploader}`,
  )
  expect(queryByTitle(t('skinlib.show.edit'))).not.toBeInTheDocument()
  expect(queryByText(t('user.setAsAvatar'))).not.toBeInTheDocument()
})

test('uploader is not existed', async () => {
  window.blessing.extra.nickname = null
  fetch.get.mockResolvedValue({ data: fixtureSkin })

  const { queryByText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
  expect(queryByText(t('general.unexistent-user'))).toBeInTheDocument()
})

test('badges', async () => {
  window.blessing.extra.badges = [
    { text: 'STAFF', color: 'primary' },
  ] as Badge[]
  fetch.get.mockResolvedValue({ data: fixtureSkin })

  const { queryByText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
  expect(queryByText('STAFF')).toBeInTheDocument()
})

test('apply to player', async () => {
  window.blessing.extra.currentUid = 2
  window.blessing.extra.inCloset = true
  fetch.get
    .mockResolvedValueOnce({ data: fixtureSkin })
    .mockResolvedValueOnce({ data: [] })

  const { getByText, getByLabelText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  fireEvent.click(getByText(t('skinlib.apply')))
  fireEvent.click(getByLabelText('Close'))

  expect(fetch.get).toBeCalledTimes(2)
})

test('set as avatar', async () => {
  window.blessing.extra.currentUid = fixtureSkin.uploader + 1
  fetch.get.mockResolvedValue({ data: fixtureSkin })
  fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

  const { getByText, getByRole, queryByText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  fireEvent.click(getByText(t('user.setAsAvatar')))
  fireEvent.click(getByText(t('general.confirm')))
  await waitFor(() => expect(fetch.post).toBeCalledTimes(1))

  expect(queryByText('ok')).toBeInTheDocument()
  expect(getByRole('status')).toHaveClass('alert-success')
})

describe('download texture', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('allowed', async () => {
    const { getByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.download')))
  })

  it('not allowed', async () => {
    window.blessing.extra.download = false
    const { queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    expect(queryByText(t('skinlib.show.download'))).not.toBeInTheDocument()
  })
})

describe('operation panel', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('uploader', async () => {
    window.blessing.extra.currentUid = fixtureSkin.uploader

    const { queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    expect(queryByText(t('skinlib.show.manage-notice'))).toBeInTheDocument()
  })

  it('administrator', async () => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    window.blessing.extra.admin = true

    const { queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    expect(queryByText(t('skinlib.show.manage-notice'))).toBeInTheDocument()
  })
})

describe('edit texture name', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('cancelled', async () => {
    const { getByText, getAllByTitle, getByDisplayValue, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[0])
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    expect(queryByText(t('skinlib.emptyNewTextureName'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
    expect(queryByText(fixtureSkin.name)).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const {
      getByText,
      getAllByTitle,
      getByDisplayValue,
      getByRole,
      queryByText,
    } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[0])
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: 't' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/rename', {
        tid: fixtureSkin.tid,
        new_name: 't',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('t')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const {
      getByText,
      getAllByTitle,
      getByDisplayValue,
      getByRole,
      queryByText,
    } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[0])
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: 't' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/rename', {
        tid: fixtureSkin.tid,
        new_name: 't',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(fixtureSkin.name)).toBeInTheDocument()
  })
})

describe('edit texture type', () => {
  beforeEach(() => {
    Object.assign(window.blessing.extra, { currentUid: fixtureSkin.uploader })
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('cancelled', async () => {
    const { getByText, getAllByTitle, getByLabelText, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[1])
    fireEvent.click(getByLabelText('Alex'))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
    expect(queryByText('steve')).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const {
      getByText,
      getAllByTitle,
      getByLabelText,
      getByRole,
      queryByText,
    } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[1])
    fireEvent.click(getByLabelText('Alex'))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/model', {
        tid: fixtureSkin.tid,
        model: 'alex',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('alex')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const {
      getByText,
      getAllByTitle,
      getByLabelText,
      getByRole,
      queryByText,
    } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[1])
    fireEvent.click(getByLabelText('Alex'))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/model', {
        tid: fixtureSkin.tid,
        model: 'alex',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('steve')).toBeInTheDocument()
  })
})

describe('add to closet', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('cancelled', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.addToCloset')))
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    expect(queryByText(t('skinlib.emptyItemName'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.addToCloset')))
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: 't' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/closet/add', {
        tid: fixtureSkin.tid,
        name: 't',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('2')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.addToCloset')))
    fireEvent.input(getByDisplayValue(fixtureSkin.name), {
      target: { value: 't' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/user/closet/add', {
        tid: fixtureSkin.tid,
        name: 't',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('1')).toBeInTheDocument()
  })
})

describe('remove from closet', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    window.blessing.extra.inCloset = true
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.removeFromCloset')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(
        `/user/closet/remove/${fixtureSkin.tid}`,
      ),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('0')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.removeFromCloset')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith(
        `/user/closet/remove/${fixtureSkin.tid}`,
      ),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('1')).toBeInTheDocument()
  })
})

describe('report texture', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('positive score', async () => {
    window.blessing.extra.report = 5

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.report.title')))
    expect(queryByText(t('skinlib.report.positive', { score: 5 })))

    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.post).not.toBeCalled()
  })

  it('negative score', async () => {
    window.blessing.extra.report = -5

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.report.title')))
    expect(queryByText(t('skinlib.report.negative', { score: 5 })))

    fireEvent.click(getByText(t('general.cancel')))
    expect(fetch.post).not.toBeCalled()
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByPlaceholderText, getByRole, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.report.title')))
    fireEvent.input(getByPlaceholderText(t('skinlib.report.reason')), {
      target: { value: 'illegal' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/report', {
        tid: fixtureSkin.tid,
        reason: 'illegal',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByPlaceholderText, getByRole, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.report.title')))
    fireEvent.input(getByPlaceholderText(t('skinlib.report.reason')), {
      target: { value: 'illegal' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/report', {
        tid: fixtureSkin.tid,
        reason: 'illegal',
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})

describe('change privacy', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader
  })

  it('cancelled', async () => {
    fetch.get.mockResolvedValue({ data: fixtureSkin })

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPrivate')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
    expect(queryByText(t('skinlib.setAsPrivate'))).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.get.mockResolvedValue({ data: fixtureSkin })
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPrivate')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/privacy', {
        tid: fixtureSkin.tid,
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.get.mockResolvedValue({ data: { ...fixtureSkin, public: false } })
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPublic')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/privacy', {
        tid: fixtureSkin.tid,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
  })
})

describe('delete texture', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader
    fetch.get.mockResolvedValue({ data: fixtureSkin })
  })

  it('cancelled', async () => {
    const { getByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.post).not.toBeCalled())
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/delete', {
        tid: fixtureSkin.tid,
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')

    jest.runAllTimers()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.post).toBeCalledWith('/skinlib/delete', {
        tid: fixtureSkin.tid,
      }),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})
