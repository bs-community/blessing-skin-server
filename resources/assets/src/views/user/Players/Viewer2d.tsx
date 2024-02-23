import React from 'react';
import styled from '@emotion/styled';
import {t} from '@/scripts/i18n';

const TexturePreview = styled.div`
  display: flex;
  justify-content: space-between;
  width: 80%;

  img {
    max-height: 64px;
    width: 64px;
  }
`;

type Properties = {
	readonly skin: string;
	readonly cape: string;
	readonly children: React.ReactNode;
};

const Viewer2d: React.FC<Properties> = properties => (
	<div className='card'>
		<div className='card-header'>
			<h3 className='card-title'>{t('general.texturePreview')}</h3>
		</div>
		<div className='card-body'>
			<TexturePreview className='mb-5'>
				<span>{t('general.skin')}</span>
				{properties.skin ? (
					<img src={properties.skin} alt={t('general.skin')}/>
				) : (
					<span>{t('user.player.texture-empty')}</span>
				)}
			</TexturePreview>
			<TexturePreview className='mt-5'>
				<span>{t('general.cape')}</span>
				{properties.cape ? (
					<img src={properties.cape} alt={t('general.cape')}/>
				) : (
					<span>{t('user.player.texture-empty')}</span>
				)}
			</TexturePreview>
		</div>
		<div className='card-footer'>{properties.children}</div>
	</div>
);

export default Viewer2d;
