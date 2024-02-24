/** @jsxImportSource @emotion/react */
import type React from 'react';
import {useState, useEffect} from 'react';
import {css} from '@emotion/react';

export type ToastType = 'success' | 'info' | 'warning' | 'error';

type Properties = {
	readonly type: ToastType;
	readonly distance: number;
	onClose(): void | Promise<void>;
	readonly children: React.ReactNode;
};

const icons = new Map<ToastType, string>([
	['success', 'check'],
	['info', 'info'],
	['warning', 'exclamation-triangle'],
	['error', 'times-circle'],
]);

const wrapper = css`
  position: fixed;
  right: calc((100% - 350px) / 2);
  width: 350px;
  z-index: 1050;
  transition-property: top;
  transition-duration: 0.3s;
`;
const shadow = css`
  box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
`;

const Toast: React.FC<Properties> = properties => {
	const [show, setShow] = useState(false);

	useEffect(() => {
		const timer = setTimeout(() => {
			setShow(true);
		}, 100);

		return () => {
			clearTimeout(timer);
		};
	}, [properties.onClose]);

	const type = properties.type === 'error' ? 'danger' : properties.type;

	const classes = [
		`alert alert-${type}`,
		'd-flex justify-content-between',
		'fade',
	];
	if (show) {
		classes.push('show');
	}

	const role = type === 'success' || type === 'info' ? 'status' : 'alert';

	return (
		<div css={wrapper} style={{top: `${properties.distance}px`}}>
			<div className={classes.join(' ')} css={shadow} role={role}>
				<span className='mr-1 d-flex align-items-center'>
					<i className={`icon fas fa-${icons.get(properties.type)}`}/>
				</span>
				<span>{properties.children}</span>
				<button
					type='button'
					className='mr-2 ml-1 close'
					onClick={properties.onClose}
				>
					&times;
				</button>
			</div>
		</div>
	);
};

export default Toast;
