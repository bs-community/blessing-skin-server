import React from 'react'
import { t } from '@/scripts/i18n'
import type { Plugin } from './types'

interface Props {
  plugin: Plugin
  isInstalling: boolean
  onInstall(): void
  onUpdate(): void
}

const Row: React.FC<Props> = (props) => {
  const { plugin, isInstalling } = props

  const allDeps = Object.entries(plugin.dependencies.all)
  const unsatisfied = Object.keys(plugin.dependencies.unsatisfied)

  return (
    <tr>
      <td style={{ width: '18%' }}>
        <div>
          <b>{plugin.title}</b>
        </div>
        <div>{plugin.name}</div>
      </td>
      <td style={{ width: '37%' }}>{plugin.description}</td>
      <td>{plugin.author}</td>
      <td>{plugin.version}</td>
      <td style={{ width: '100px' }}>
        {allDeps.length === 0 ? (
          <i>{t('admin.noDependencies')}</i>
        ) : (
          <div className="d-flex flex-column">
            {allDeps.map(([name, constraint]) => {
              const classes = [
                'mb-1',
                'badge',
                `bg-${unsatisfied.includes(name) ? 'red' : 'green'}`,
              ]
              return (
                <span key={name} className={classes.join(' ')}>
                  {name}: {constraint}
                </span>
              )
            })}
          </div>
        )}
      </td>
      <td style={{ width: '12%' }}>
        {plugin.can_update ? (
          <button
            className="btn btn-success"
            disabled={isInstalling}
            onClick={props.onUpdate}
          >
            {isInstalling ? (
              <>
                <i className="fas fa-spinner fa-spin mr-1"></i>
                {t('admin.pluginUpdating')}
              </>
            ) : (
              <>
                <i className="fas fa-sync-alt mr-1"></i>
                {t('admin.updatePlugin')}
              </>
            )}
          </button>
        ) : (
          <button
            className="btn btn-default"
            disabled={props.isInstalling || !!plugin.installed}
            onClick={props.onInstall}
          >
            {isInstalling ? (
              <>
                <i className="fas fa-spinner fa-spin mr-1"></i>
                {t('admin.pluginInstalling')}
              </>
            ) : (
              <>
                <i className="fas fa-download mr-1"></i>
                {t('admin.installPlugin')}
              </>
            )}
          </button>
        )}
      </td>
    </tr>
  )
}

export default Row
