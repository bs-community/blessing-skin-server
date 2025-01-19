import {t} from '@/scripts/i18n';
import React from 'react';
import * as scoreUtils from './scoreUtils';

type Props = {
	readonly isLoading: boolean;
	readonly lastSign: Date;
	readonly canSignAfterZero: boolean;
	readonly signGap: number;
	readonly onClick: React.MouseEventHandler<HTMLButtonElement>;
};

const SignButton: React.FC<Props> = props => {
	const {lastSign, signGap, canSignAfterZero} = props;
	const remainingTime = scoreUtils.remainingTime(
		lastSign,
		signGap,
		canSignAfterZero,
	);
	const remainingTimeText = scoreUtils.remainingTimeText(remainingTime);
	const canSign = remainingTime <= 0;

	return (
		<button
			className='btn bg-gradient-primary pl-4 pr-4'
			role='button'
			disabled={!canSign || props.isLoading}
			onClick={props.onClick}
		>
			<i className='far fa-calendar-check' aria-hidden='true'/>
			{' '}
&nbsp;
			{canSign ? t('user.sign') : remainingTimeText}
		</button>
	);
};

export default React.memo(SignButton);
