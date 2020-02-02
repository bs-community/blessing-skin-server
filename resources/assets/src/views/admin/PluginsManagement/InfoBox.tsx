import React from 'react'
import { trans } from '../../../scripts/i18n'
import { Plugin } from './types'
import styles from './InfoBox.scss'

interface Props {
  plugin: Plugin
  onEnable(plugin: Plugin): void
  onDisable(plugin: Plugin): void
  onDelete(plugin: Plugin): void
  baseUrl: string
}

const InfoBox: React.FC<Props> = props => {
  const { plugin } = props

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    event.preventDefault()

    if (event.target.checked) {
      props.onEnable(plugin)
    } else {
      props.onDisable(plugin)
    }
  }

  const handleDelete = () => props.onDelete(plugin)

  return (
    <div className={`info-box mr-3 ${styles.box}`}>
      <span className={`info-box-icon bg-${plugin.icon.bg}`}>
        <i className={`${plugin.icon.faType} fa-${plugin.icon.fa}`} />
      </span>
      <div className={`info-box-content ${styles.content}`}>
        <div className="d-flex justify-content-between">
          <div>
            <input
              className="mr-2"
              type="checkbox"
              checked={plugin.enabled}
              title={
                plugin.enabled
                  ? trans('admin.disablePlugin')
                  : trans('admin.enablePlugin')
              }
              onChange={handleChange}
            />
            <strong className="mr-2">{plugin.title}</strong>
            <span className="text-gray">v{plugin.version}</span>
          </div>
          <div className={styles.actions}>
            {plugin.readme && (
              <a
                href={`${props.baseUrl}/admin/plugins/readme/${plugin.name}`}
                title={trans('admin.pluginReadme')}
              >
                <i className="fas fa-question" />
              </a>
            )}
            {plugin.enabled && plugin.config && (
              <a
                href={`${props.baseUrl}/admin/plugins/config/${plugin.name}`}
                title={trans('admin.configurePlugin')}
              >
                <i className="fas fa-cog" />
              </a>
            )}
            <a
              href="#"
              title={trans('admin.deletePlugin')}
              onClick={handleDelete}
            >
              <i className="fas fa-trash" />
            </a>
          </div>
        </div>
        <div className={`mt-2 ${styles.description}`}>{plugin.description}</div>
      </div>
    </div>
  )
}

export default React.memo(InfoBox)
