import React from 'react';

type Props = {
	readonly name: string;
	readonly icon: string;
	readonly color: string;
	readonly used: number;
	readonly unused: number;
	readonly unit: string;
};

const InfoBox: React.FC<Props> = props => {
	const total = Math.trunc(props.used + props.unused);
	const percentage = (props.used / total) * 100;

	return (
		<div className={`info-box bg-${props.color}`}>
			<span className='info-box-icon'>
				<i className={`fas fa-${props.icon}`}/>
			</span>
			<div className='info-box-content'>
				<span className='info-box-text'>{props.name}</span>
				<span className='info-box-number'>
					<b>{props.used}</b>
					{' '}
					/
					{total}
					{' '}
					{props.unit}
				</span>
				<div className='progress'>
					<div className='progress-bar' style={{width: `${percentage}%`}}/>
				</div>
			</div>
		</div>
	);
};

export default React.memo(InfoBox);
