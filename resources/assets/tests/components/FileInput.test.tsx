import FileInput from '@/components/FileInput';
import {t} from '@/scripts/i18n';
import {fireEvent, render} from '@testing-library/react';
import {expect} from 'vitest';

it('click to select file', () => {
	const {getAllByText} = render(
<FileInput
	file={null}
	onChange={() => {
				/* */
			}}
		/>
);

	fireEvent.click(getAllByText(t('skinlib.upload.select-file'))[1]);
});

it('display file name', () => {
	const file = new File([], 'f.txt');
	const {queryByText} = render(
<FileInput
	file={file}
	onChange={() => {
				/* */
			}}
		/>
);
	expect(queryByText('f.txt')).toBeInTheDocument();
});

it('input file', () => {
	const mock = vi.fn();

	const {getByLabelText} = render(<FileInput file={null} onChange={mock}/>);
	fireEvent.change(getByLabelText(t('skinlib.upload.select-file')));

	expect(mock).toBeCalled();
});
