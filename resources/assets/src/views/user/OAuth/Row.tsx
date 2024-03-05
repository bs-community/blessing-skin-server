
import type {App} from './types';
import {t} from '@/scripts/i18n';
import ButtonEdit from '@/components/ButtonEdit';

type Properties = {
	readonly app: App;
	readonly onEditName: React.MouseEventHandler<HTMLAnchorElement>;
	readonly onEditRedirect: React.MouseEventHandler<HTMLAnchorElement>;
	readonly onDelete: React.MouseEventHandler<HTMLButtonElement>;
};

const Row: React.FC<Properties> = properties => {
	const {app} = properties;

	return (
		<tr>
			<td>{app.id}</td>
			<td>
				<span>{app.name}</span>
				<ButtonEdit
					title={t('user.oauth.modifyName')}
					onClick={properties.onEditName}
				/>
			</td>
			<td>{app.secret}</td>
			<td>
				<span>{app.redirect}</span>
				<ButtonEdit
					title={t('user.oauth.modifyUrl')}
					onClick={properties.onEditRedirect}
				/>
			</td>
			<td>
				<button className='btn btn-danger' onClick={properties.onDelete}>
					{t('report.delete')}
				</button>
			</td>
		</tr>
	);
};

export default Row;
