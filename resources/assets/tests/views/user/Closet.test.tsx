import React from 'react'
import { render, fireEvent, wait } from '@testing-library/react'
import $ from 'jquery'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { ClosetItem, Player } from '@/scripts/types'
import Closet from '@/views/user/Closet'

jest.mock('@/scripts/net')

const fixtureSkin: Readonly<ClosetItem> = Object.freeze<ClosetItem>({
  tid: 1,
  name: 'skin',
  type: 'steve',
  hash: 'abc',
  size: 2,
  uploader: 1,
  public: true,
  upload_at: new Date().toString(),
  likes: 1,
  pivot: {
    user_uid: 1,
    texture_tid: 1,
    item_name: 'closet_skin',
  },
})

const fixtureCape: Readonly<ClosetItem> = Object.freeze<ClosetItem>({
  tid: 2,
  name: 'cape',
  type: 'cape',
  hash: 'def',
  size: 2,
  uploader: 1,
  public: true,
  upload_at: new Date().toString(),
  likes: 1,
  pivot: {
    user_uid: 1,
    texture_tid: 2,
    item_name: 'closet_cape',
  },
})

const fixturePlayer: Readonly<Player> = Object.freeze<Player>({
  pid: 1,
  name: 'kumiko',
  tid_skin: 1,
  tid_cape: 2,
})

beforeEach(() => {
  const container = document.createElement('div')
  container.id = 'previewer'
  document.body.appendChild(container)
})

afterEach(() => {
  document.querySelector('#previewer')!.remove()
})

test('loading indicator', () => {
  fetch.get.mockResolvedValue({
    data: { items: [], category: 'skin', total_pages: 1 },
  })
  const { queryByTitle } = render(<Closet />)
  expect(queryByTitle('Loading...')).toBeInTheDocument()
})

test('empty closet', async () => {
  fetch.get.mockResolvedValue({
    data: { items: [], category: 'skin', total_pages: 0 },
  })

  const { queryByText } = render(<Closet />)
  await wait()
  expect(queryByText(/skin library/i)).toBeInTheDocument()
})

test('categories', async () => {
  fetch.get
    .mockResolvedValueOnce({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })
    .mockResolvedValueOnce({
      data: { items: [fixtureCape], category: 'cape', total_pages: 1 },
    })
    .mockResolvedValueOnce({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })

  const { getByText, queryByText } = render(<Closet />)
  await wait()
  expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
  expect(queryByText(fixtureCape.pivot.item_name)).not.toBeInTheDocument()

  fireEvent.click(getByText(t('general.cape')))
  await wait()
  expect(queryByText(fixtureSkin.pivot.item_name)).not.toBeInTheDocument()
  expect(queryByText(fixtureCape.pivot.item_name)).toBeInTheDocument()

  fireEvent.click(getByText(t('general.skin')))
  await wait()
  expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
  expect(queryByText(fixtureCape.pivot.item_name)).not.toBeInTheDocument()
})

test('search textures', async () => {
  fetch.get
    .mockResolvedValueOnce({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })
    .mockResolvedValueOnce({
      data: { items: [], category: 'skin', total_pages: 0 },
    })

  const { getByPlaceholderText, queryByText } = render(<Closet />)
  await wait()

  fireEvent.input(getByPlaceholderText(t('user.typeToSearch')), {
    target: { value: 'abc' },
  })
  await wait()

  expect(queryByText(/no result/i)).toBeInTheDocument()
})

test('switch page', async () => {
  fetch.get
    .mockResolvedValueOnce({
      data: { items: [], category: 'skin', total_pages: 2 },
    })
    .mockResolvedValueOnce({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 2 },
    })

  const { getByText, queryByText } = render(<Closet />)
  await wait()
  fireEvent.click(getByText('2'))
  await wait()

  expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
})

describe('rename item', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Closet />,
    )
    await wait()

    fireEvent.click(getByText(t('user.renameItem')))
    fireEvent.input(getByDisplayValue(fixtureSkin.pivot.item_name), {
      target: { value: 'my skin' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith(
      `/user/closet/rename/${fixtureSkin.tid}`,
      { name: 'my skin' },
    )
    expect(queryByText('my skin')).toBeInTheDocument()
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('empty name', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByDisplayValue, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.renameItem')))
    fireEvent.input(getByDisplayValue(fixtureSkin.pivot.item_name), {
      target: { value: '' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).not.toBeCalled()
    expect(queryByText(t('skinlib.emptyNewTextureName'))).toBeInTheDocument()

    fireEvent.click(getByText(t('general.cancel')))
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByDisplayValue, getByRole, queryByText } = render(
      <Closet />,
    )
    await wait()

    fireEvent.click(getByText(t('user.renameItem')))
    fireEvent.input(getByDisplayValue(fixtureSkin.pivot.item_name), {
      target: { value: 'my skin' },
    })
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith(
      `/user/closet/rename/${fixtureSkin.tid}`,
      { name: 'my skin' },
    )
    expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('cancelled', async () => {
    const { getByText, getByDisplayValue, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.renameItem')))
    fireEvent.input(getByDisplayValue(fixtureSkin.pivot.item_name), {
      target: { value: 'my skin' },
    })
    fireEvent.click(getByText(t('general.cancel')))
    await wait()

    expect(fetch.post).not.toBeCalled()
    expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
  })
})

describe('remove item', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByRole, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.removeItem')))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith(`/user/closet/remove/${fixtureSkin.tid}`)
    expect(queryByText(/skin library/i)).toBeInTheDocument()
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.removeItem')))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith(`/user/closet/remove/${fixtureSkin.tid}`)
    expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('cancelled', async () => {
    const { getByText, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.removeItem')))
    fireEvent.click(getByText(t('general.cancel')))
    await wait()

    expect(fetch.post).not.toBeCalled()
    expect(queryByText(fixtureSkin.pivot.item_name)).toBeInTheDocument()
  })
})

describe('select textures', () => {
  beforeEach(() => {
    fetch.get
      .mockResolvedValueOnce({
        data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
      })
      .mockResolvedValueOnce({
        data: { items: [fixtureCape], category: 'cape', total_pages: 1 },
      })
  })

  it('select skin', async () => {
    const { getByAltText, queryAllByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    expect(queryAllByText(t('general.skin'))).toHaveLength(2)
  })

  it('select cape', async () => {
    const { getByText, getByAltText, queryAllByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('general.cape')))
    await wait()

    fireEvent.click(getByAltText(fixtureCape.pivot.item_name))
    expect(queryAllByText(t('general.cape'))).toHaveLength(2)
  })

  it('reset selected', async () => {
    const { getByText, getByAltText, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    fireEvent.click(getByText(t('general.cape')))
    await wait()
    fireEvent.click(getByAltText(fixtureCape.pivot.item_name))

    expect(
      queryByText(`${t('general.skin')} & ${t('general.cape')}`),
    ).toBeInTheDocument()

    fireEvent.click(getByText(t('user.resetSelected')))
    expect(
      queryByText(`${t('general.skin')} & ${t('general.cape')}`),
    ).not.toBeInTheDocument()
  })
})

describe('set avatar', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue({
      data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
    })

    const img = document.createElement('img')
    img.alt = 'User Image'
    document.body.appendChild(img)
  })

  afterEach(() => {
    document.querySelector('[alt="User Image"]')!.remove()
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const { getByText, getByRole, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.setAsAvatar')))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith('/user/profile/avatar', {
      tid: fixtureSkin.tid,
    })
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
    expect(document.querySelector('[alt="User Image"]')).toHaveAttribute('src')
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, getByRole, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.setAsAvatar')))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalledWith('/user/profile/avatar', {
      tid: fixtureSkin.tid,
    })
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('cancelled', async () => {
    const { getByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.setAsAvatar')))
    fireEvent.click(getByText(t('general.cancel')))
    await wait()

    expect(fetch.post).not.toBeCalled()
  })
})

describe('apply textures to player', () => {
  it('selected nothing', async () => {
    fetch.get.mockResolvedValue({
      data: { items: [], category: 'skin', total_pages: 1 },
    })

    const { getByText, getByRole, queryByText } = render(<Closet />)
    await wait()

    fireEvent.click(getByText(t('user.useAs')))

    expect(queryByText(t('user.emptySelectedTexture'))).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-info')
  })

  it('search players', async () => {
    fetch.get
      .mockResolvedValueOnce({
        data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
      })
      .mockResolvedValueOnce({ data: [fixturePlayer] })

    const {
      getByText,
      getByAltText,
      getAllByPlaceholderText,
      queryByText,
    } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    fireEvent.click(getByText(t('user.useAs')))

    await wait()
    expect(queryByText(fixturePlayer.name)).toBeInTheDocument()

    fireEvent.input(getAllByPlaceholderText(t('user.typeToSearch'))[1], {
      target: { value: 'reina' },
    })
    expect(queryByText(fixturePlayer.name)).not.toBeInTheDocument()
  })

  it('succeeded', async () => {
    fetch.get
      .mockResolvedValueOnce({
        data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
      })
      .mockResolvedValueOnce({ data: [fixturePlayer] })
    fetch.post.mockResolvedValue({ code: 0, message: 'success' })

    const {
      getByText,
      getByAltText,
      getByTitle,
      getByRole,
      queryByText,
    } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    fireEvent.click(getByText(t('user.useAs')))
    await wait()
    fireEvent.click(getByTitle(fixturePlayer.name))
    await wait()

    expect(fetch.post).toBeCalledWith(`/user/player/set/${fixturePlayer.pid}`, {
      skin: fixtureSkin.tid,
    })
    expect(queryByText('success')).toBeInTheDocument()
    expect(getByRole('status')).toHaveClass('alert-success')
  })

  it('failed', async () => {
    fetch.get
      .mockResolvedValueOnce({
        data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
      })
      .mockResolvedValueOnce({ data: [fixturePlayer] })
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const {
      getByText,
      getByAltText,
      getByTitle,
      getByRole,
      queryByText,
    } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    fireEvent.click(getByText(t('user.useAs')))
    await wait()
    fireEvent.click(getByTitle(fixturePlayer.name))
    await wait()

    expect(fetch.post).toBeCalledWith(`/user/player/set/${fixturePlayer.pid}`, {
      skin: fixtureSkin.tid,
    })
    expect(queryByText('failed')).toBeInTheDocument()
    expect(getByRole('alert')).toHaveClass('alert-danger')
  })

  it('close dialog', async () => {
    fetch.get
      .mockResolvedValueOnce({
        data: { items: [fixtureSkin], category: 'skin', total_pages: 1 },
      })
      .mockResolvedValueOnce({ data: [fixturePlayer] })

    const { getByText, getByAltText } = render(<Closet />)
    await wait()

    fireEvent.click(getByAltText(fixtureSkin.pivot.item_name))
    fireEvent.click(getByText(t('user.useAs')))
    await wait()

    $('#modal-apply')
      .modal('hide')
      .trigger('hidden.bs.modal')

    expect(fetch.post).not.toBeCalled()
  })
})
