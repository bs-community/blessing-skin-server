type AlertType = 'success' | 'info' | 'warning' | 'danger';

const icons = new Map<AlertType, string>([
	['success', 'check'],
	['info', 'info'],
	['warning', 'exclamation-triangle'],
	['danger', 'times-circle'],
]);

type Props = {
	readonly type: AlertType;
	readonly children?: React.ReactNode;
};

const Alert: React.FC<Props> = ({type, children}) => {
	const icon = icons.get(type);

	return children === ''
		? null
		: (
			<div className={`alert alert-${type}`}>
				<i className={`icon fas fa-${icon}`}/>
				{children}
			</div>
		);
};

export default Alert;
