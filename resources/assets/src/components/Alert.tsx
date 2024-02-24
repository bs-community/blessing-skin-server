import type React from 'react';

type AlertType = 'success' | 'info' | 'warning' | 'danger';

const icons = new Map<AlertType, string>([
	['success', 'check'],
	['info', 'info'],
	['warning', 'exclamation-triangle'],
	['danger', 'times-circle'],
]);

type Properties = {
	readonly type: AlertType;
	readonly children?: React.ReactNode;
};

const Alert: React.FC<Properties> = properties => {
	const {type} = properties;
	const icon = icons.get(type);

	return properties.children ? (
		<div className={`alert alert-${type}`}>
			<i className={`icon fas fa-${icon}`}/>
			{properties.children}
		</div>
	) : null;
};

export default Alert;
