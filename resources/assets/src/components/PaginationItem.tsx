
type Properties = {
	readonly disabled?: boolean;
	readonly active?: boolean;
	readonly title?: string;
	readonly className?: string;
	onClick?(): void;
	readonly children?: React.ReactNode;
};

const PaginationItem: React.FC<Properties> = properties => {
	const classes = ['page-item'];
	if (properties.active) {
		classes.push('active');
	}

	if (properties.disabled) {
		classes.push('disabled');
	}

	if (properties.className) {
		classes.push(properties.className);
	}

	const handleClick = (event: React.MouseEvent) => {
		event.preventDefault();
		if (!properties.disabled && properties.onClick) {
			properties.onClick();
		}
	};

	return (
		<li className={classes.join(' ')} title={properties.title} onClick={handleClick}>
			<a href='#' className='page-link' aria-disabled={properties.disabled}>
				{properties.children}
			</a>
		</li>
	);
};

export default PaginationItem;
