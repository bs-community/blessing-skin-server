
export type Props = {
	readonly text?: string;
	readonly dangerousHTML?: string;
	readonly children?: React.ReactNode;
};

const ModalContent: React.FC<Props> = props => {
	if (props.children) {
		return <>{props.children}</>;
	}

	if (props.text) {
		return (
			<>
				{props.text.split(/\r?\n/).map((line, i) =>
					<p key={i}>{line}</p>)}
			</>
		);
	}

	if (props.dangerousHTML) {
		return <div dangerouslySetInnerHTML={{__html: props.dangerousHTML}}/>;
	}

	return <></>;
};

export default ModalContent;
