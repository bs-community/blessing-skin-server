import React from 'react';
import ModalContent, {type Props as ContentProperties} from './ModalContent';
import ModalInput, {
	type
	Props as InputProperties, type
	InternalProps as InputInteralProperties,
} from './ModalInput';

type InternalProperties = {
	readonly showInput: boolean;
};

export type Props = ContentProperties & InputProperties;

const ModalBody: React.FC<InternalProperties & InputInteralProperties & Props> = properties => (
	<div className='modal-body'>
		<ModalContent text={properties.text} dangerousHTML={properties.dangerousHTML}>
			{properties.children}
		</ModalContent>
		{properties.showInput && <ModalInput {...properties}/>}
	</div>
);

export default ModalBody;
