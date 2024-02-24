import type React from 'react';

type Properties = {
	readonly active?: boolean;
	readonly bg?: string;
};

type Attributes = React.DetailedHTMLProps<
React.ButtonHTMLAttributes<HTMLButtonElement>,
HTMLButtonElement
>;

const Button: React.FC<Properties & Attributes> = properties => {
	const classes = [properties.className ?? ''];
	if (properties.bg) {
		classes.push('btn', `bg-${properties.bg}`);
	}

	if (properties.active) {
		classes.push('active');
	}

	const rest = {...properties, active: undefined, bg: undefined};

	return (
		<button {...rest} className={classes.join(' ')}>
			{properties.children}
		</button>
	);
};

export default Button;
