import React from 'react'
import { t } from '@/scripts/i18n'
import { Line } from './types'
import styles from './Row.module.scss'

interface Props {
  line: Line
  onEdit(line: Line): void
  onRemove(line: Line): void
}

const Row: React.FC<Props> = (props) => {
  const { line, onEdit, onRemove } = props
  const text = line.text[blessing.locale]

  const handleEditClick = () => onEdit(line)

  const handleRemoveClick = () => onRemove(line)

  return (
    <tr>
      <td className={styles.group}>{line.group}</td>
      <td className={styles.key}>{line.key}</td>
      <td>{text || t('admin.i18n.empty')}</td>
      <td className={styles.operations}>
        <button className="btn btn-default mr-2" onClick={handleEditClick}>
          {t('admin.i18n.modify')}
        </button>
        <button className="btn btn-danger" onClick={handleRemoveClick}>
          {t('admin.i18n.delete')}
        </button>
      </td>
    </tr>
  )
}

export default Row
