/** @jsxImportSource @emotion/react */
import {css} from '@emotion/react';
import {t} from '@/scripts/i18n';
import type {Player} from '@/scripts/types';
import ButtonEdit from '@/components/ButtonEdit';
import * as cssUtils from '@/styles/utils';

type Properties = {
	player: Player;
	selected: boolean;
	onClick: React.MouseEventHandler;
	onEditName(player: Player): Promise<void>;
	onReset(): void;
	onDelete(player: Player): Promise<void>;
};

const Row: React.FC<Properties> = properties => {
	const {player} = properties;

	const handleEdit = () => {
		properties.onEditName(player);
	};

	const handleDelete = () => {
		properties.onDelete(player);
	};

	const selected
    = properties.selected
    && css`
      background: #efefef;
      .dark-mode & {
        background: var(--dark);
      }
    `;

	return (
		<tr css={[cssUtils.pointerCursor, selected]} onClick={properties.onClick}>
			<td>{player.pid}</td>
			<td>
				<span>{player.name}</span>
				<ButtonEdit title={t('user.player.edit-pname')} onClick={handleEdit}/>
			</td>
			<td className='d-flex'>
				<button className='btn btn-warning' onClick={properties.onReset}>
					{t('user.player.delete-texture')}
				</button>
				<button className='btn btn-danger ml-2' onClick={handleDelete}>
					{t('user.player.delete-player')}
				</button>
			</td>
		</tr>
	);
};

export default Row;
