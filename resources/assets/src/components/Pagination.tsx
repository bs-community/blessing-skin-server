import { t } from '@/scripts/i18n'
import PaginationItem from './PaginationItem'

interface Props {
  page: number
  totalPages: number
  onChange(page: number): void | Promise<void>
}

const labels = {
  prev: '‹',
  next: '›',
}

const Pagination: React.FC<Props> = (props) => {
  const { page, totalPages, onChange } = props

  if (totalPages < 1) {
    return null
  }

  return (
    <ul className="pagination">
      <PaginationItem
        title={t('vendor.datatable.prev')}
        disabled={page === 1}
        onClick={() => onChange(page - 1)}
      >
        {labels.prev}
        <span className="d-inline d-sm-none ml-1">
          {t('vendor.datatable.prev')}
        </span>
      </PaginationItem>
      {totalPages < 8 ? (
        Array.from({ length: totalPages }).map((_, i) => (
          <PaginationItem
            key={i}
            className="d-none d-sm-block"
            active={page === i + 1}
            onClick={() => onChange(i + 1)}
          >
            {i + 1}
          </PaginationItem>
        ))
      ) : (
        <>
          {page < 4 ? (
            [1, 2, 3, 4].map((n) => (
              <PaginationItem
                key={n}
                className="d-none d-sm-block"
                active={page === n}
                onClick={() => onChange(n)}
              >
                {n}
              </PaginationItem>
            ))
          ) : (
            <PaginationItem
              className="d-none d-sm-block"
              onClick={() => onChange(1)}
            >
              1
            </PaginationItem>
          )}
          <PaginationItem className="d-none d-sm-block" disabled>
            ...
          </PaginationItem>
          {page > 3 && page < totalPages - 2 && (
            <>
              {[page - 1, page, page + 1].map((n) => (
                <PaginationItem
                  key={n}
                  className="d-none d-sm-block"
                  active={page === n}
                  onClick={() => onChange(n)}
                >
                  {n}
                </PaginationItem>
              ))}
              <PaginationItem className="d-none d-sm-block" disabled>
                ...
              </PaginationItem>
            </>
          )}
          {totalPages - page < 3 ? (
            [totalPages - 3, totalPages - 2, totalPages - 1, totalPages].map(
              (n) => (
                <PaginationItem
                  key={n}
                  className="d-none d-sm-block"
                  active={page === n}
                  onClick={() => onChange(n)}
                >
                  {n}
                </PaginationItem>
              ),
            )
          ) : (
            <PaginationItem
              className="d-none d-sm-block"
              onClick={() => onChange(totalPages)}
            >
              {totalPages}
            </PaginationItem>
          )}
        </>
      )}
      <PaginationItem
        title={t('vendor.datatable.next')}
        disabled={page === totalPages}
        onClick={() => onChange(page + 1)}
      >
        <span className="d-inline d-sm-none mr-1">
          {t('vendor.datatable.next')}
        </span>
        {labels.next}
      </PaginationItem>
    </ul>
  )
}

export default Pagination
