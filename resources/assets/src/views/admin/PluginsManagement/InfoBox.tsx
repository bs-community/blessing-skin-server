
import styled from '@emotion/styled';
import clsx from 'clsx';
import type {Plugin} from './types';
import {t} from '@/scripts/i18n';

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
`;
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
`;
const Header = styled.div`
  max-width: calc(100% - 40px);
  display: flex;
  align-items: center;
`;
const Description = styled.div`
  font-size: 14px;
`;

type Properties = {
	readonly plugin: Plugin;
	onEnable(plugin: Plugin): void;
	onDisable(plugin: Plugin): void;
	onDelete(plugin: Plugin): void;
	readonly baseUrl: string;
};

const InfoBox: React.FC<Properties> = properties => {
	const {plugin} = properties;

	const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		event.preventDefault();

		if (event.target.checked) {
			properties.onEnable(plugin);
		} else {
			properties.onDisable(plugin);
		}
	};

	const handleDelete = () => {
		properties.onDelete(plugin);
	};

	const isDarkMode = document.body.classList.contains('dark-mode');

	return (
		<Box className={clsx('info-box', 'mr-3', {'bg-gray-dark': isDarkMode})}>
			<span className={`info-box-icon bg-${plugin.icon.bg}`}>
				<i className={`${plugin.icon.faType} fa-${plugin.icon.fa}`}/>
			</span>
			<div className='info-box-content'>
				<div className='d-flex justify-content-between'>
					<Header>
						<input
							className='mr-2 d-inline-block'
							type='checkbox'
							checked={plugin.enabled}
							title={
								plugin.enabled
									? t('admin.disablePlugin')
									: t('admin.enablePlugin')
							}
							onChange={handleChange}
						/>
						<strong className='d-inline-block mr-2 text-truncate'>
							{plugin.title}
						</strong>
						<span className='d-none d-sm-inline-block text-gray'>
							v{plugin.version}
						</span>
					</Header>
					<div>
						{plugin.readme && (
							<ActionButton
								href={`${properties.baseUrl}/admin/plugins/readme/${plugin.name}`}
								title={t('admin.pluginReadme')}
							>
								<i className='fas fa-question'/>
							</ActionButton>
						)}
						{plugin.enabled && plugin.config && (
							<ActionButton
								href={`${properties.baseUrl}/admin/plugins/config/${plugin.name}`}
								title={t('admin.configurePlugin')}
							>
								<i className='fas fa-cog'/>
							</ActionButton>
						)}
						<ActionButton
							href='#'
							title={t('admin.deletePlugin')}
							onClick={handleDelete}
						>
							<i className='fas fa-trash'/>
						</ActionButton>
					</div>
				</div>
				<Description className='mt-2 text-truncate' title={plugin.description}>
					{plugin.description}
				</Description>
			</div>
		</Box>
	);
};

export default InfoBox;
