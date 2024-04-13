/** @jsxImportSource @emotion/react */
import {useRef} from 'react';
import {css} from '@emotion/react';
import {t} from '@/scripts/i18n';

const hideRawBrowseButton = css`
  ::after {
    display: none;
  }
`;

type Properties = {
	file: File | undefined;
	accept?: string;
	onChange(event: React.ChangeEvent<HTMLInputElement>): void;
};

const FileInput: React.FC<Properties> = properties => {
	const reference = useRef<HTMLInputElement>(null);

	const handleClick = () => {
		reference.current!.click();
	};

	return (
		<div className='form-group'>
			<label htmlFor='select-file'>{t('skinlib.upload.select-file')}</label>
			<div className='input-group'>
				<div className='custom-file'>
					<input
						ref={reference}
						type='file'
						className='custom-file-input'
						id='select-file'
						accept={properties.accept}
						title={t('skinlib.upload.select-file')}
						onChange={properties.onChange}
					/>
					<label className='custom-file-label' css={hideRawBrowseButton}>
						{properties.file?.name}
					</label>
				</div>
				<div className='input-group-append'>
					<button className='btn btn-default' onClick={handleClick}>
						{t('skinlib.upload.select-file')}
					</button>
				</div>
			</div>
		</div>
	);
};

export default FileInput;
