import React from 'react'
import { render, fireEvent, waitFor } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { Texture, TextureType } from '@/scripts/types'
import urls from '@/scripts/urls'
import Show, { Badge } from '@/views/skinlib/Show'

jest.mock('@/scripts/net')

const fixtureSkin: Readonly<Texture> = Object.freeze<Texture>({
  tid: 1,
  name: 'skin',
  type: TextureType.Steve,
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
  type: TextureType.Cape,
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
    uploaderExists: true,
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
  fetch.get.mockResolvedValue(fixtureSkin)

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
  fetch.get.mockResolvedValue(fixtureCape)

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
  window.blessing.extra.nickname = 'not existed'
  window.blessing.extra.uploaderExists = false
  fetch.get.mockResolvedValue(fixtureSkin)

  const { queryByText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
  expect(queryByText('not existed')).toBeInTheDocument()
})

test('badges', async () => {
  window.blessing.extra.badges = [
    { text: 'STAFF', color: 'primary' },
  ] as Badge[]
  fetch.get.mockResolvedValue(fixtureSkin)

  const { queryByText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))
  expect(queryByText('STAFF')).toBeInTheDocument()
})

test('apply to player', async () => {
  window.blessing.extra.currentUid = 2
  window.blessing.extra.inCloset = true
  fetch.get.mockResolvedValueOnce(fixtureSkin).mockResolvedValueOnce([])

  const { getByText, getByLabelText } = render(<Show />)
  await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

  fireEvent.click(getByText(t('skinlib.apply')))
  fireEvent.click(getByLabelText('Close'))

  expect(fetch.get).toBeCalledTimes(2)
})

test('set as avatar', async () => {
  window.blessing.extra.currentUid = fixtureSkin.uploader + 1
  fetch.get.mockResolvedValue(fixtureSkin)
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
    fetch.get.mockResolvedValue(fixtureSkin)
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
    fetch.get.mockResolvedValue(fixtureSkin)
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
    fetch.get.mockResolvedValue(fixtureSkin)
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
    await waitFor(() => expect(fetch.put).not.toBeCalled())
    expect(queryByText(fixtureSkin.name)).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

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
      expect(fetch.put).toBeCalledWith(urls.texture.name(fixtureSkin.tid), {
        name: 't',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('t')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

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
      expect(fetch.put).toBeCalledWith(urls.texture.name(fixtureSkin.tid), {
        name: 't',
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
    fetch.get.mockResolvedValue(fixtureSkin)
  })

  it('cancelled', async () => {
    const { getByText, getAllByTitle, getByLabelText, queryByText } = render(
      <Show />,
    )
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getAllByTitle(t('skinlib.show.edit'))[1])
    fireEvent.click(getByLabelText('Alex'))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.put).not.toBeCalled())
    expect(queryByText('steve')).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

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
      expect(fetch.put).toBeCalledWith(urls.texture.type(fixtureSkin.tid), {
        type: 'alex',
      }),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('alex')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

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
      expect(fetch.put).toBeCalledWith(urls.texture.type(fixtureSkin.tid), {
        type: 'alex',
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
    fetch.get.mockResolvedValue(fixtureSkin)
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
      expect(fetch.post).toBeCalledWith(urls.user.closet.add(), {
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
      expect(fetch.post).toBeCalledWith(urls.user.closet.add(), {
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
    fetch.get.mockResolvedValue(fixtureSkin)
  })

  it('succeeded', async () => {
    fetch.del.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.removeFromCloset')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(`/user/closet/${fixtureSkin.tid}`),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText('0')).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.del.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.removeFromCloset')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(`/user/closet/${fixtureSkin.tid}`),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText('1')).toBeInTheDocument()
  })
})

describe('report texture', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader + 1
    fetch.get.mockResolvedValue(fixtureSkin)
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
    fetch.get.mockResolvedValue(fixtureSkin)

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPrivate')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.put).not.toBeCalled())
    expect(queryByText(t('skinlib.setAsPrivate'))).toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.get.mockResolvedValue(fixtureSkin)
    fetch.put.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPrivate')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.texture.privacy(fixtureSkin.tid)),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.get.mockResolvedValue({ ...fixtureSkin, public: false })
    fetch.put.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPublic')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.texture.privacy(fixtureSkin.tid)),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
  })

  it('duplicated texture with confirmed', async () => {
    fetch.get.mockResolvedValue({ ...fixtureSkin, public: false })
    fetch.put.mockResolvedValue({
      code: 2,
      message: 'duplicated',
      data: { tid: 2 },
    })

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPublic')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.texture.privacy(fixtureSkin.tid)),
    )
    expect(queryByText('duplicated')).toBeInTheDocument()
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
    fireEvent.click(getByText(t('user.viewInSkinlib')))
  })

  it('duplicated texture with cancelled', async () => {
    fetch.get.mockResolvedValue({ ...fixtureSkin, public: false })
    fetch.put.mockResolvedValue({
      code: 2,
      message: 'duplicated',
      data: { tid: 2 },
    })

    const { getByText, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.setAsPublic')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.put).toBeCalledWith(urls.texture.privacy(fixtureSkin.tid)),
    )

    // temporary workaround for multiple modals exist
    document.querySelectorAll('.modal')[0].remove()

    expect(queryByText('duplicated')).toBeInTheDocument()
    expect(queryByText(t('skinlib.setAsPublic'))).toBeInTheDocument()
    fireEvent.click(getByText(t('general.cancel')))
  })
})

describe('delete texture', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = fixtureSkin.uploader
    fetch.get.mockResolvedValue(fixtureSkin)
  })

  it('cancelled', async () => {
    const { getByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.cancel')))
    await waitFor(() => expect(fetch.del).not.toBeCalled())
  })

  it('succeeded', async () => {
    fetch.del.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(urls.texture.delete(fixtureSkin.tid)),
    )
    expect(queryByText('ok')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')

    jest.runAllTimers()
  })

  it('failed', async () => {
    fetch.del.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Show />)
    await waitFor(() => expect(fetch.get).toBeCalledTimes(1))

    fireEvent.click(getByText(t('skinlib.show.delete-texture')))
    fireEvent.click(getByText(t('general.confirm')))
    await waitFor(() =>
      expect(fetch.del).toBeCalledWith(urls.texture.delete(fixtureSkin.tid)),
    )
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })
})
