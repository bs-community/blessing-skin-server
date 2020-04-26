import React from 'react'
import PaginationItem from './PaginationItem'

interface Props {
  page: number
  totalPages: number
  onChange(page: number): void | Promise<void>
}

export const labels = {
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
      <PaginationItem disabled={page === 1} onClick={() => onChange(page - 1)}>
        {labels.prev}
      </PaginationItem>
      {totalPages < 8 ? (
        Array.from({ length: totalPages }).map((_, i) => (
          <PaginationItem
            key={i}
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
                active={page === n}
                onClick={() => onChange(n)}
              >
                {n}
              </PaginationItem>
            ))
          ) : (
            <PaginationItem onClick={() => onChange(1)}>1</PaginationItem>
          )}
          <PaginationItem disabled>...</PaginationItem>
          {page > 3 && page < totalPages - 2 && (
            <>
              {[page - 1, page, page + 1].map((n) => (
                <PaginationItem
                  key={n}
                  active={page === n}
                  onClick={() => onChange(n)}
                >
                  {n}
                </PaginationItem>
              ))}
              <PaginationItem disabled>...</PaginationItem>
            </>
          )}
          {totalPages - page < 3 ? (
            [totalPages - 3, totalPages - 2, totalPages - 1, totalPages].map(
              (n) => (
                <PaginationItem
                  key={n}
                  active={page === n}
                  onClick={() => onChange(n)}
                >
                  {n}
                </PaginationItem>
              ),
            )
          ) : (
            <PaginationItem onClick={() => onChange(totalPages)}>
              {totalPages}
            </PaginationItem>
          )}
        </>
      )}
      <PaginationItem
        disabled={page === totalPages}
        onClick={() => onChange(page + 1)}
      >
        {labels.next}
      </PaginationItem>
    </ul>
  )
}

export default Pagination
