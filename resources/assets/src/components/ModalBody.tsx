
import ModalContent, {type Props as ContentProps} from './ModalContent';
import ModalInput, {
	type
	InternalProps as InputInteralProps,
	type
	Props as InputProps,
} from './ModalInput';

type InternalProps = {
	readonly showInput: boolean;
};

export type Props = ContentProps & InputProps;

const ModalBody: React.FC<InternalProps & InputInteralProps & Props> = props => (
	<div className='modal-body'>
		<ModalContent text={props.text} dangerousHTML={props.dangerousHTML}>
			{props.children}
		</ModalContent>
		{props.showInput && <ModalInput {...props}/>}
	</div>
);

export default ModalBody;
