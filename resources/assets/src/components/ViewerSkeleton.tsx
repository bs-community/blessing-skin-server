import {t} from '@/scripts/i18n';

export default function ViewerSkeleton() {
	return (
		<div className='card'>
			<div className='card-header'>
				<div className='d-flex justify-content-between'>
					<h3 className='card-title'>
						<span>{t('general.texturePreview')}</span>
					</h3>
				</div>
			</div>
			<div className='card-body'/>
		</div>
	);
}

