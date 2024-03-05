
export type Props = {
	readonly text?: string;
	readonly dangerousHTML?: string;
	readonly children?: React.ReactNode;
};

const ModalContent: React.FC<Props> = properties => {
	if (properties.children) {
		return <>{properties.children}</>;
	}

	if (properties.text) {
		return (
			<>
				{properties.text.split(/\r?\n/).map((line, i) => (
					<p key={i}>{line}</p>
				))}
			</>
		);
	}

	if (properties.dangerousHTML) {
		return <div dangerouslySetInnerHTML={{__html: properties.dangerousHTML}}/>;
	}

	return <></>;
};

export default ModalContent;
