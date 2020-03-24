import React from 'react'
import { t } from '@/scripts/i18n'
import Button from './Button'
import { Filter } from './types'
import { humanizeType } from './utils'

interface Props {
  filter: Filter
  onChange(filter: Filter): void
}

const FilterSelector: React.FC<Props> = (props) => {
  const { filter, onChange } = props

  const handleSkinClick = () => onChange('skin')
  const handleSteveClick = () => onChange('steve')
  const handleAlexClick = () => onChange('alex')
  const handleCapeClick = () => onChange('cape')

  return (
    <>
      <button
        className="btn btn-default dropdown-toggle"
        type="button"
        data-toggle="dropdown"
      >
        {humanizeType(filter)}
      </button>
      <div className="dropdown-menu">
        <Button
          className="dropdown-item"
          active={filter === 'skin'}
          onClick={handleSkinClick}
        >
          {t('general.skin')}
        </Button>
        <Button
          className="dropdown-item"
          active={filter === 'steve'}
          onClick={handleSteveClick}
        >
          Steve
        </Button>
        <Button
          className="dropdown-item"
          active={filter === 'alex'}
          onClick={handleAlexClick}
        >
          Alex
        </Button>
        <Button
          className="dropdown-item"
          active={filter === 'cape'}
          onClick={handleCapeClick}
        >
          {t('general.cape')}
        </Button>
      </div>
    </>
  )
}

export default FilterSelector
