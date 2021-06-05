import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast } from '@/scripts/notify'
import { Paginator, TextureType } from '@/scripts/types'
import urls from '@/scripts/urls'
import Loading from '@/components/Loading'
import Pagination from '@/components/Pagination'
import addClosetItem from '../Show/addClosetItem'
import removeClosetItem from '@/views/user/Closet/removeClosetItem'
import FilterSelector from './FilterSelector'
import Button from './Button'
import Item from './Item'
import type { Filter, LibraryItem } from './types'

const SkinLibrary: React.FC = () => {
  const [isLoading, setIsLoading] = useState(true)
  const [items, setItems] = useState<LibraryItem[]>([])
  const [closet, setCloset] = useState<number[]>([])
  const [filter, setFilter] = useState<Filter>('skin')
  const [name, setName] = useState('')
  const [keyword, setKeyword] = useState('')
  const [uploader, setUploader] = useState<number | null>(0)
  const [sort, setSort] = useState('time')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const currentUid = useBlessingExtra<number | null>('currentUid', null)

  useEmitMounted()

  useEffect(() => {
    const parseSearch = (query: string) => {
      const search = new URLSearchParams(query)

      const filter = search.get('filter') ?? ''
      setFilter(
        [
          'skin',
          TextureType.Steve,
          TextureType.Alex,
          TextureType.Cape,
        ].includes(filter)
          ? (filter as Filter)
          : 'skin',
      )

      const keyword = decodeURIComponent(search.get('keyword') ?? '')
      setName(keyword)
      setKeyword(keyword)

      const uploader = search.get('uploader') ?? '0'
      setUploader(Number.parseInt(uploader))

      setSort(search.get('sort') ?? 'time')

      setPage(Number.parseInt(search.get('page') ?? '1'))
    }

    parseSearch(location.search)

    const handler = (event: PopStateEvent) => parseSearch(event.state)
    window.addEventListener('popstate', handler)

    return () => {
      window.removeEventListener('popstate', handler)
    }
  }, [])

  useEffect(() => {
    const getItems = async () => {
      setIsLoading(true)

      const search = new URLSearchParams()
      search.append('filter', filter)
      if (keyword) {
        search.append('keyword', keyword)
      }
      if (uploader) {
        search.append('uploader', uploader.toString())
      }
      search.append('sort', sort)
      search.append('page', page.toString())
      window.history.pushState(search.toString(), '', `?${search}`)

      const result = await fetch.get<Paginator<LibraryItem>>(
        urls.skinlib.list(),
        search,
      )
      setItems(result.data)
      setTotalPages(result.last_page)
      setIsLoading(false)
    }
    getItems()
  }, [filter, keyword, uploader, sort, page])

  useEffect(() => {
    const getCloset = async () => {
      const closet = await fetch.get<number[]>(urls.user.closet.ids())
      setCloset(closet)
    }
    if (currentUid) {
      getCloset()
    }
  }, [currentUid])

  const handleFilterChange = (filter: Filter) => setFilter(filter)

  const handleNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setName(event.target.value)
  }

  const handleFormSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setKeyword(name)
  }

  const handleLikesSortClick = () => setSort('likes')
  const handleTimeSortClick = () => setSort('time')
  const handleSelfUploadClick = () => setUploader(currentUid)
  const handleResetClick = () => {
    setFilter('skin')
    setName('')
    setKeyword('')
    setSort('time')
    setUploader(0)
    setPage(1)
  }

  const handleUploaderClick = (uploader: number) => setUploader(uploader)

  const handleAddToCloset = async (item: LibraryItem, index: number) => {
    if (!currentUid) {
      toast.warning(t('skinlib.anonymous'))
      return
    }

    const ok = await addClosetItem(item)
    if (ok) {
      setCloset((closet) => [...closet, item.tid])
      setItems((items) => {
        items[index] = { ...item, likes: item.likes + 1 }
        return items.slice()
      })
    }
  }

  const handleRemoveFromCloset = async (item: LibraryItem, index: number) => {
    const ok = await removeClosetItem(item.tid)
    if (ok) {
      setCloset((closet) => closet.filter((id) => id !== item.tid))
      setItems((items) => {
        items[index] = { ...item, likes: item.likes - 1 }
        return items.slice()
      })
    }
  }

  return (
    <div className="container">
      <div className="content-header">
        <div className="container-fluid d-flex justify-content-between">
          <h1>{t('general.skinlib')}</h1>
          <span>
            {uploader ? (
              <>
                <i className="fas fa-user mr-1"></i>
                {t('skinlib.filter.uploader', { uid: uploader })}
              </>
            ) : (
              <>
                <i className="fas fa-user-friends mr-1"></i>
                {t('skinlib.filter.allUsers')}
              </>
            )}
          </span>
        </div>
      </div>
      <section className="content">
        <div className="card">
          <div className="card-body">
            <div className="form-group pt-0 mb-3 d-flex justify-content-between">
              <form onSubmit={handleFormSubmit}>
                <div className="input-group">
                  <div className="input-group-prepend">
                    <FilterSelector
                      filter={filter}
                      onChange={handleFilterChange}
                    />
                  </div>
                  <input
                    type="text"
                    inputMode="search"
                    className="form-control"
                    value={name}
                    placeholder={t('vendor.datatable.search')}
                    onChange={handleNameChange}
                  />
                  <div className="input-group-append">
                    <button
                      className="btn btn-primary px-3"
                      type="submit"
                      title={t('vendor.datatable.search')}
                    >
                      <i className="fas fa-search"></i>
                    </button>
                  </div>
                </div>
              </form>
              <div className="d-none d-sm-block">
                <div className="btn-group">
                  <Button
                    bg="olive"
                    active={sort === 'likes'}
                    onClick={handleLikesSortClick}
                  >
                    {t('skinlib.sort.likes')}
                  </Button>
                  <Button
                    bg="olive"
                    active={sort === 'time'}
                    onClick={handleTimeSortClick}
                  >
                    {t('skinlib.sort.time')}
                  </Button>
                  {currentUid !== null && (
                    <Button
                      bg="olive"
                      active={uploader === currentUid}
                      onClick={handleSelfUploadClick}
                    >
                      {t('skinlib.seeMyUpload')}
                    </Button>
                  )}
                  <Button bg="olive" onClick={handleResetClick}>
                    {t('skinlib.reset')}
                  </Button>
                </div>
              </div>
            </div>
            {items.length > 0 ? (
              <div className="d-flex flex-wrap">
                {items.map((item, i) => (
                  <Item
                    key={item.tid}
                    item={item}
                    liked={closet.includes(item.tid)}
                    onAdd={(item) => handleAddToCloset(item, i)}
                    onRemove={(item) => handleRemoveFromCloset(item, i)}
                    onUploaderClick={handleUploaderClick}
                  />
                ))}
              </div>
            ) : (
              <p className="text-center m-5">{t('general.noResult')}</p>
            )}
          </div>
          <div className="card-footer">
            <div className="d-flex justify-content-center">
              <Pagination
                page={page}
                totalPages={totalPages}
                onChange={setPage}
              />
            </div>
          </div>
          {isLoading && (
            <div className="overlay">
              <Loading />
            </div>
          )}
        </div>
      </section>
    </div>
  )
}

export default hot(SkinLibrary)
