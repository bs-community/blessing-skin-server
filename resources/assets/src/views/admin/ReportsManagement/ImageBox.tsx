
import styled from '@emotion/styled';
import {type Report, Status} from './types';
import {t} from '@/scripts/i18n';
import type {Texture} from '@/scripts/types';

const Card = styled.div`
  width: 240px;
  transition-property: box-shadow;
  transition-duration: 0.3s;

  .card-body {
    flex: unset;
    display: flex;
    justify-content: center;
  }

  img {
    cursor: pointer;
    width: 170px;
    height: 170px;
  }

  .card-footer {
    flex: 1 1 auto;
    * {
      margin: 2.5px 0;
    }
  }
`;

type Properties = {
	readonly report: Report;
	onClick(texture: Texture | undefined): void;
	onBan(): void;
	onDelete(): void;
	onReject(): void;
};

const ImageBox: React.FC<Properties> = properties => {
	const {report} = properties;
	const preview = `${blessing.base_url}/preview/${report.tid}?height=150`;
	const previewPNG = `${preview}&png`;

	const handleImageClick = () => {
		properties.onClick(report.texture);
	};

	return (
		<Card className='card mr-3 mb-3'>
			<div className='card-header'>
				<b>
					{t('skinlib.show.uploader')}
					{': '}
				</b>
				<span className='mr-1'>{report.texture_uploader?.nickname}</span>
				(UID: {report.uploader})
			</div>
			<div className='card-body'>
				<picture>
					<source srcSet={preview} type='image/webp'/>
					<img
						src={previewPNG}
						alt={report.tid.toString()}
						className='card-img-top'
						onClick={handleImageClick}
					/>
				</picture>
			</div>
			<div className='card-footer'>
				<div className='d-flex justify-content-between'>
					<div>
						{report.status === Status.Pending ? (
							<span className='badge bg-warning'>{t('report.status.0')}</span>
						) : (report.status === Status.Resolved ? (
							<span className='badge bg-success'>{t('report.status.1')}</span>
						) : (
							<span className='badge bg-danger'>{t('report.status.2')}</span>
						))}
						<span className='badge bg-info ml-1'>TID: {report.tid}</span>
					</div>
					<div className='dropdown'>
						<a
							className='text-gray'
							href='#'
							data-toggle='dropdown'
							aria-expanded='false'
						>
							<i className='fas fa-cog'/>
						</a>
						<div className='dropdown-menu dropdown-menu-right'>
							<a
								href={`${blessing.base_url}/skinlib/show/${report.tid}`}
								className='dropdown-item'
								target='_blank' rel='noreferrer'
							>
								<i className='fas fa-share-square mr-2'/>
								{t('user.viewInSkinlib')}
							</a>
							<a href='#' className='dropdown-item' onClick={properties.onBan}>
								<i className='fas fa-user-slash mr-2'/>
								{t('report.ban')}
							</a>
							<a
								href='#'
								className='dropdown-item dropdown-item-danger'
								onClick={properties.onDelete}
							>
								<i className='fas fa-trash mr-2'/>
								{t('skinlib.show.delete-texture')}
							</a>
							<a href='#' className='dropdown-item' onClick={properties.onReject}>
								<i className='fas fa-thumbs-down mr-2'/>
								{t('report.reject')}
							</a>
						</div>
					</div>
				</div>
				<div>
					<b>
						{t('report.reporter')}
						{': '}
					</b>
					<span className='mr-1'>{report.informer?.nickname}</span>
					(UID: {report.reporter})
				</div>
				<details>
					<summary className='text-truncate'>
						<b>
							{t('report.reason')}
							{': '}
						</b>
						{report.reason}
					</summary>
					<div>{report.reason}</div>
					<div>
						<small>
							{t('report.time')}
							{': '}
							{report.report_at}
						</small>
					</div>
				</details>
			</div>
		</Card>
	);
};

export default ImageBox;
