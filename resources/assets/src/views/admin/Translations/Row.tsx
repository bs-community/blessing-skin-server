import styled from '@emotion/styled';
import type {Line} from './types';
import {t} from '@/scripts/i18n';

const Group = styled.td`
  width: 15%;
`;
const Key = styled.td`
  width: 20%;
`;
const Operations = styled.td`
  width: 25%;
`;

type Properties = {
	readonly line: Line;
	onEdit(line: Line): void;
	onRemove(line: Line): void;
};

const Row: React.FC<Properties> = properties => {
	const {line, onEdit, onRemove} = properties;
	const text = line.text[blessing.locale];

	const handleEditClick = () => {
		onEdit(line);
	};

	const handleRemoveClick = () => {
		onRemove(line);
	};

	return (
		<tr>
			<Group>{line.group}</Group>
			<Key>{line.key}</Key>
			<td>{text || t('admin.i18n.empty')}</td>
			<Operations>
				<button className='btn btn-default mr-2' onClick={handleEditClick}>
					{t('admin.i18n.modify')}
				</button>
				<button className='btn btn-danger' onClick={handleRemoveClick}>
					{t('admin.i18n.delete')}
				</button>
			</Operations>
		</tr>
	);
};

export default Row;
