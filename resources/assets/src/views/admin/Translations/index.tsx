import React, { useState, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import { useImmer } from 'use-immer'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal, toast } from '@/scripts/notify'
import type { Paginator } from '@/scripts/types'
import Loading from '@/components/Loading'
import Pagination from '@/components/Pagination'
import type { Line } from './types'
import Row from './Row'

const Translations: React.FC = () => {
  const [lines, setLines] = useImmer<Line[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)

  useEffect(() => {
    const getLines = async () => {
      setIsLoading(true)
      const result = await fetch.get<Paginator<Line>>('/admin/i18n/list', {
        page,
      })
      setLines(() => result.data)
      setTotalPages(result.last_page)
      setIsLoading(false)
    }
    getLines()
  }, [page])

  const handleEdit = async (line: Line, index: number) => {
    let text: string
    try {
      const { value } = await showModal({
        mode: 'prompt',
        text: t('admin.i18n.updating'),
        input: line.text[blessing.locale],
      })
      text = value
    } catch {
      return
    }

    const { code, message } = await fetch.put<fetch.ResponseBody>(
      `/admin/i18n/${line.id}`,
      { text },
    )
    if (code === 0) {
      toast.success(message)
      setLines((lines) => {
        lines[index]!.text[blessing.locale] = text
      })
    } else {
      toast.error(message)
    }
  }

  const handleRemove = async (line: Line) => {
    try {
      await showModal({
        text: t('admin.i18n.confirmDelete'),
        okButtonType: 'danger',
      })
    } catch {
      return
    }

    const { message } = await fetch.del(`/admin/i18n/${line.id}`)
    toast.success(message)
    const { id } = line
    setLines((lines) => lines.filter((line) => line.id !== id))
  }

  return (
    <>
      <div className="card-body p-0">
        <table className="table table-striped">
          <thead>
            <tr>
              <th>{t('admin.i18n.group')}</th>
              <th>{t('admin.i18n.key')}</th>
              <th>{t('admin.i18n.text')}</th>
              <th>{t('admin.operationsTitle')}</th>
            </tr>
          </thead>
          <tbody>
            {isLoading ? (
              <tr>
                <td className="text-center" colSpan={4}>
                  <Loading />
                </td>
              </tr>
            ) : lines.length === 0 ? (
              <tr>
                <td className="text-center" colSpan={4}>
                  {t('general.noResult')}
                </td>
              </tr>
            ) : (
              lines.map((line, i) => (
                <Row
                  key={line.id}
                  line={line}
                  onEdit={(line) => handleEdit(line, i)}
                  onRemove={handleRemove}
                />
              ))
            )}
          </tbody>
        </table>
      </div>
      <div className="card-footer d-flex flex-row-reverse">
        <Pagination page={page} totalPages={totalPages} onChange={setPage} />
      </div>
    </>
  )
}

export default hot(Translations)
