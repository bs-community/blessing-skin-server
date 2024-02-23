import React from 'react';
import styled from '@emotion/styled';
import type {LibraryItem} from './types';
import {humanizeType} from './utils';
import {t} from '@/scripts/i18n';
import * as cssUtils from '@/styles/utils';

const Card = styled.div`
  width: 245px;
  transition-property: box-shadow;
  transition-duration: 0.3s;
  &:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  }

  .card-body {
    background-color: #eff1f0;
  }

  img {
    height: 210px;
  }
`;

const Icon = styled.i`
  height: 24px;
`;

const Badge = styled.span`
  padding-top: 0.4rem;
`;

const NickNameBadge = styled(Badge)`
  ${cssUtils.pointerCursor}
  max-width: 100px;
`;

type ButtonLikeProperties = {
	liked: boolean;
};
const ButtonLike = styled.a<ButtonLikeProperties>`
  ${cssUtils.pointerCursor}

  i, span {
    color: ${properties => (properties.liked ? '#dc3545' : '#6c757d')};
    &:hover {
      color: ${properties => (properties.liked ? '#dc3545' : '#343a40')};
    }
  }
`;

type Properties = {
	readonly item: LibraryItem;
	readonly liked: boolean;
	onAdd(texture: LibraryItem): Promise<void>;
	onRemove(texture: LibraryItem): Promise<void>;
	onUploaderClick(uploader: number): void;
};

const Item: React.FC<Properties> = properties => {
	const {item} = properties;

	const link = `${blessing.base_url}/skinlib/show/${item.tid}`;
	const preview = `${blessing.base_url}/preview/${item.tid}?height=150`;
	const previewPNG = `${preview}&png`;

	const handleUploaderClick = (event: React.MouseEvent) => {
		event.preventDefault();
		properties.onUploaderClick(item.uploader);
	};

	const handleHeartClick = (event: React.MouseEvent) => {
		event.preventDefault();
		properties.liked ? properties.onRemove(item) : properties.onAdd(item);
	};

	return (
		<a href={link} className='ml-3 mr-2 mb-2 d-block' target='_blank' rel='noreferrer'>
			<Card className='card'>
				<div className='card-body'>
					<a href={link} target='_blank' rel='noreferrer'>
						<picture>
							<source srcSet={preview} type='image/webp'/>
							<img src={previewPNG} alt={item.name} className='card-img-top'/>
						</picture>
					</a>
				</div>
				<div className='card-footer'>
					<div className='d-flex align-items-center'>
						{item.public || (
							<Icon
								className='fas fa-lock text-warning mr-2'
								title={t('skinlib.private')}
							/>
						)}
						<span className='d-block mb-1 text-truncate' title={item.name}>
							{item.name}
						</span>
					</div>
					<div className='d-flex justify-content-between'>
						<div className='d-flex'>
							<Badge className='badge bg-teal mr-1'>
								{humanizeType(item.type)}
							</Badge>
							<NickNameBadge
								className='badge bg-indigo text-truncate'
								title={t('skinlib.show.uploader')}
								onClick={handleUploaderClick}
							>
								{item.nickname}
							</NickNameBadge>
						</div>
						<ButtonLike
							liked={properties.liked}
							tabIndex={-1}
							onClick={handleHeartClick}
						>
							<i className='fas fa-heart mr-1'/>
							<span>{item.likes}</span>
						</ButtonLike>
					</div>
				</div>
			</Card>
		</a>
	);
};

export default Item;
