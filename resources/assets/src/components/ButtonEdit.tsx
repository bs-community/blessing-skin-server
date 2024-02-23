import React from 'react';

type Properties = {
	readonly title?: string;
	readonly onClick: React.MouseEventHandler<HTMLAnchorElement>;
};

const ButtonEdit: React.FC<Properties> = properties => (
	<a href='#' title={properties.title} className='ml-2' onClick={properties.onClick}>
		<i className='fas fa-edit'/>
	</a>
);

export default ButtonEdit;
