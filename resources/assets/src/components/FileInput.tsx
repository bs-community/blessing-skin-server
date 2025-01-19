import {t} from '@/scripts/i18n';
import {css} from '@emotion/react';
import {useRef} from 'react';

const hideRawBrowseButton = css`
  ::after {
    display: none;
  }
`;

type Props = {
	file: File | undefined;
	accept?: string;
	onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
};

const FileInput: React.FC<Props> = props => {
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
						accept={props.accept}
						title={t('skinlib.upload.select-file')}
						onChange={props.onChange}
					/>
					<label className='custom-file-label' css={hideRawBrowseButton}>
						{props.file?.name}
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
