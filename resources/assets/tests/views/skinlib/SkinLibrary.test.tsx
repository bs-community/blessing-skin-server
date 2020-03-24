import React from 'react'
import { render, fireEvent, wait } from '@testing-library/react'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { Paginator } from '@/scripts/types'
import SkinLibrary from '@/views/skinlib/SkinLibrary'
import { LibraryItem } from '@/views/skinlib/SkinLibrary/types'

jest.mock('@/scripts/net')

const fixtureItem: Readonly<LibraryItem> = Object.freeze<LibraryItem>({
  tid: 1,
  name: 'my skin',
  type: 'steve',
  uploader: 1,
  nickname: 'me',
  public: true,
  likes: 70,
})

function createPaginator(data: LibraryItem[]): Paginator<LibraryItem> {
  return {
    data,
    total: data.length,
    from: 1,
    to: data.length,
    current_page: 1,
    last_page: 1,
  }
}

beforeEach(() => {
  window.blessing.extra = { currentUid: null }
})

test('without authenticated', async () => {
  fetch.get.mockResolvedValue(createPaginator([]))

  const { queryByText } = render(<SkinLibrary />)
  await wait()

  expect(fetch.get).toBeCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('filter')).toBe('skin')
      expect(search.get('sort')).toBe('time')
      expect(search.get('page')).toBe('1')
      return true
    }),
  )
  expect(fetch.get).not.toBeCalledWith('/user/closet/ids')
  expect(queryByText(t('skinlib.seeMyUpload'))).not.toBeInTheDocument()
})

test('search by keyword', async () => {
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByText, getByPlaceholderText } = render(<SkinLibrary />)
  await wait()

  fireEvent.input(getByPlaceholderText(t('vendor.datatable.search')), {
    target: { value: 'k' },
  })
  fireEvent.click(getByText(t('general.submit')))
  await wait()

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('keyword')).toBe('k')
      return true
    }),
  )
})

test('select uploaded by self', async () => {
  window.blessing.extra.currentUid = 1
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByText, queryByText } = render(<SkinLibrary />)
  await wait()

  fireEvent.click(getByText(t('skinlib.seeMyUpload')))
  await wait()

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('uploader')).toBe('1')
      return true
    }),
  )
  expect(queryByText(t('skinlib.filter.uploader', { uid: 1 })))
})

test('reset query', async () => {
  window.blessing.extra.currentUid = 1
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByText, getByPlaceholderText, queryByText } = render(
    <SkinLibrary />,
  )
  await wait()

  fireEvent.click(getByText('Steve'))
  await wait()
  fireEvent.input(getByPlaceholderText(t('vendor.datatable.search')), {
    target: { value: 'k' },
  })
  fireEvent.click(getByText(t('general.submit')))
  await wait()
  fireEvent.click(getByText(t('skinlib.seeMyUpload')))
  await wait()
  fireEvent.click(getByText(t('skinlib.sort.likes')))
  await wait()
  fireEvent.click(getByText(t('skinlib.reset')))
  await wait()

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('filter')).toBe('skin')
      expect(search.get('keyword')).toBeNull()
      expect(search.get('uploader')).toBeNull()
      expect(search.get('sort')).toBe('time')
      expect(search.get('page')).toBe('1')
      return true
    }),
  )
  expect(queryByText(t('skinlib.filter.uploader', { uid: 1 })))
})

test('browser goes back', async () => {
  fetch.get.mockResolvedValue(createPaginator([]))

  const { getByText } = render(<SkinLibrary />)
  await wait()

  fireEvent.click(getByText('Steve'))
  await wait()

  const state: URLSearchParams = window.history.state
  state.set('filter', 'skin')
  const event = new PopStateEvent('popstate', { state })
  window.dispatchEvent(event)
  await wait()

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('filter')).toBe('skin')
      return true
    }),
  )
})

test('pagination', async () => {
  const response = { ...createPaginator([]), last_page: 2 }
  fetch.get.mockResolvedValue(response)

  const { getByText } = render(<SkinLibrary />)
  await wait()

  fireEvent.click(getByText('2'))

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('page')).toBe('2')
      return true
    }),
  )
})

test('library item', async () => {
  fetch.get.mockResolvedValue(createPaginator([fixtureItem]))

  const { getByText, queryByText, queryAllByText, queryByAltText } = render(
    <SkinLibrary />,
  )
  await wait()

  expect(queryAllByText('Steve')).toHaveLength(2)
  expect(queryByText(fixtureItem.name)).toBeInTheDocument()
  expect(queryByAltText(fixtureItem.name)).toHaveAttribute(
    'src',
    `/preview/${fixtureItem.tid}?height=150`,
  )
  expect(queryByText(fixtureItem.nickname)).toBeInTheDocument()

  fireEvent.click(getByText(fixtureItem.nickname))
  await wait()

  expect(fetch.get).toHaveBeenLastCalledWith(
    '/skinlib/list',
    expect.toSatisfy((search: URLSearchParams) => {
      expect(search.get('uploader')).toBe(fixtureItem.uploader.toString())
      return true
    }),
  )
  const search = new URLSearchParams(location.search)
  expect(search.get('uploader')).toBe(fixtureItem.uploader.toString())
})

test('private texture', async () => {
  const item = { ...fixtureItem, public: false }
  fetch.get.mockResolvedValue(createPaginator([item]))

  const { queryByText } = render(<SkinLibrary />)
  await wait()

  expect(queryByText(t('skinlib.private'))).toBeInTheDocument()
})

describe('by filter', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([]))
  })

  it('skin', async () => {
    const { getByText, queryAllByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText('Steve'))
    await wait()
    fireEvent.click(getByText(t('general.skin')))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('filter')).toBe('skin')
        return true
      }),
    )
    expect(queryAllByText(t('general.skin'))).toHaveLength(2)
    const search = new URLSearchParams(location.search)
    expect(search.get('filter')).toBe('skin')
  })

  it('steve', async () => {
    const { getByText, queryAllByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText('Steve'))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('filter')).toBe('steve')
        return true
      }),
    )
    expect(queryAllByText('Steve')).toHaveLength(2)
    const search = new URLSearchParams(location.search)
    expect(search.get('filter')).toBe('steve')
  })

  it('alex', async () => {
    const { getByText, queryAllByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText('Alex'))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('filter')).toBe('alex')
        return true
      }),
    )
    expect(queryAllByText('Alex')).toHaveLength(2)
    const search = new URLSearchParams(location.search)
    expect(search.get('filter')).toBe('alex')
  })

  it('cape', async () => {
    const { getByText, queryAllByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(t('general.cape')))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('filter')).toBe('cape')
        return true
      }),
    )
    expect(queryAllByText(t('general.cape'))).toHaveLength(2)
    const search = new URLSearchParams(location.search)
    expect(search.get('filter')).toBe('cape')
  })
})

describe('sorting', () => {
  beforeEach(() => {
    fetch.get.mockResolvedValue(createPaginator([]))
  })

  it('by time', async () => {
    const { getByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(t('skinlib.sort.likes')))
    await wait()
    fireEvent.click(getByText(t('skinlib.sort.time')))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('sort')).toBe('time')
        return true
      }),
    )
    const search = new URLSearchParams(location.search)
    expect(search.get('sort')).toBe('time')
  })

  it('by likes', async () => {
    const { getByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(t('skinlib.sort.likes')))
    await wait()

    expect(fetch.get).toHaveBeenLastCalledWith(
      '/skinlib/list',
      expect.toSatisfy((search: URLSearchParams) => {
        expect(search.get('sort')).toBe('likes')
        return true
      }),
    )
    const search = new URLSearchParams(location.search)
    expect(search.get('sort')).toBe('likes')
  })
})

describe('add to closet', () => {
  beforeEach(() => {
    fetch.get.mockImplementation((url: string) => {
      if (url === '/skinlib/list') {
        return Promise.resolve(createPaginator([fixtureItem]))
      } else {
        return Promise.resolve([])
      }
    })
  })

  it('without authenticated', async () => {
    const { getByText, queryByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(fixtureItem.likes.toString()))
    expect(queryByText(t('skinlib.anonymous'))).toBeInTheDocument()
    expect(fetch.post).not.toBeCalled()
  })

  it('succeeded', async () => {
    window.blessing.extra.currentUid = 1
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(fixtureItem.likes.toString()))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalled()
    expect(queryByText((fixtureItem.likes + 1).toString())).toBeInTheDocument()
  })

  it('failed', async () => {
    window.blessing.extra.currentUid = 1
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, queryByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(fixtureItem.likes.toString()))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalled()
    expect(queryByText(fixtureItem.likes.toString())).toBeInTheDocument()
  })
})

describe('remove from closet', () => {
  beforeEach(() => {
    window.blessing.extra.currentUid = 1
    fetch.get.mockImplementation((url: string) => {
      if (url === '/skinlib/list') {
        return Promise.resolve(createPaginator([fixtureItem]))
      } else {
        return Promise.resolve([fixtureItem.tid])
      }
    })
  })

  it('succeeded', async () => {
    fetch.post.mockResolvedValue({ code: 0, message: 'ok' })

    const { getByText, queryByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(fixtureItem.likes.toString()))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalled()
    expect(queryByText((fixtureItem.likes - 1).toString())).toBeInTheDocument()
  })

  it('failed', async () => {
    fetch.post.mockResolvedValue({ code: 1, message: 'failed' })

    const { getByText, queryByText } = render(<SkinLibrary />)
    await wait()

    fireEvent.click(getByText(fixtureItem.likes.toString()))
    fireEvent.click(getByText(t('general.confirm')))
    await wait()

    expect(fetch.post).toBeCalled()
    expect(queryByText(fixtureItem.likes.toString())).toBeInTheDocument()
  })
})
