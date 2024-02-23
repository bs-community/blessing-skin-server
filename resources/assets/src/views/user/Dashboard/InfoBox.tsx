import React from 'react';

type Properties = {
	readonly name: string;
	readonly icon: string;
	readonly color: string;
	readonly used: number;
	readonly unused: number;
	readonly unit: string;
};

const InfoBox: React.FC<Properties> = properties => {
	const total = Math.trunc(properties.used + properties.unused);
	const percentage = (properties.used / total) * 100;

	return (
		<div className={`info-box bg-${properties.color}`}>
			<span className='info-box-icon'>
				<i className={`fas fa-${properties.icon}`}/>
			</span>
			<div className='info-box-content'>
				<span className='info-box-text'>{properties.name}</span>
				<span className='info-box-number'>
					<b>{properties.used}</b> / {total} {properties.unit}
				</span>
				<div className='progress'>
					<div className='progress-bar' style={{width: `${percentage}%`}}/>
				</div>
			</div>
		</div>
	);
};

export default React.memo(InfoBox);
