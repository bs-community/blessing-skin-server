import React from 'react'
import styled from '@emotion/styled'
import { t } from '@/scripts/i18n'
import type { Plugin } from './types'

const Box = styled.div`
  cursor: default;
  transition-property: box-shadow;
  transition-duration: 0.3s;
  &:hover {
    box-shadow: 0 0.5rem 1rem rgba(#000, 0.15);
  }

  .info-box-content {
    max-width: calc(100% - 70px);
  }
`
const ActionButton = styled.a`
  transition-property: color;
  transition-duration: 0.3s;
  color: #000;
  .dark-mode & {
    color: #fff;
  }
  &:hover {
    color: #999;
  }
  &:not(:last-child) {
    margin-right: 9px;
  }
`
const Header = styled.div`
  max-width: calc(100% - 40px);
  display: flex;
`
const Description = styled.div`
  font-size: 14px;
`

interface Props {
  plugin: Plugin
  onEnable(plugin: Plugin): void
  onDisable(plugin: Plugin): void
  onDelete(plugin: Plugin): void
  baseUrl: string
}

const InfoBox: React.FC<Props> = (props) => {
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
    <Box className="info-box mr-3">
      <span className={`info-box-icon bg-${plugin.icon.bg}`}>
        <i className={`${plugin.icon.faType} fa-${plugin.icon.fa}`} />
      </span>
      <div className="info-box-content">
        <div className="d-flex justify-content-between">
          <Header>
            <input
              className="mr-2 d-inline-block"
              type="checkbox"
              checked={plugin.enabled}
              title={
                plugin.enabled
                  ? t('admin.disablePlugin')
                  : t('admin.enablePlugin')
              }
              onChange={handleChange}
            />
            <strong className="d-inline-block mr-2 text-truncate">
              {plugin.title}
            </strong>
            <span className="d-none d-sm-inline-block text-gray">
              v{plugin.version}
            </span>
          </Header>
          <div>
            {plugin.readme && (
              <ActionButton
                href={`${props.baseUrl}/admin/plugins/readme/${plugin.name}`}
                title={t('admin.pluginReadme')}
              >
                <i className="fas fa-question" />
              </ActionButton>
            )}
            {plugin.enabled && plugin.config && (
              <ActionButton
                href={`${props.baseUrl}/admin/plugins/config/${plugin.name}`}
                title={t('admin.configurePlugin')}
              >
                <i className="fas fa-cog" />
              </ActionButton>
            )}
            <ActionButton
              href="#"
              title={t('admin.deletePlugin')}
              onClick={handleDelete}
            >
              <i className="fas fa-trash" />
            </ActionButton>
          </div>
        </div>
        <Description className="mt-2 text-truncate" title={plugin.description}>
          {plugin.description}
        </Description>
      </div>
    </Box>
  )
}

export default InfoBox
