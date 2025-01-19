type Properties = {
	readonly title?: string;
	readonly onClick: React.MouseEventHandler<HTMLAnchorElement>;
};

const ButtonEdit: React.FC<Properties> = ({title, onClick}) => (
	<a href='#' title={title} className='ml-2' onClick={onClick}>
		<i className='fas fa-edit'/>
	</a>
);

export default ButtonEdit;
