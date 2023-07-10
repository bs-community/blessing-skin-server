import ModalContent from './ModalContent'
import ModalInput from './ModalInput'
import type { Props as ContentProps } from './ModalContent'
import type {
  Props as InputProps,
  InternalProps as InputInteralProps,
} from './ModalInput'

interface InternalProps {
  showInput: boolean
}

export type Props = ContentProps & InputProps

const ModalBody: React.FC<InternalProps & InputInteralProps & Props> = (
  props,
) => {
  return (
    <div className="modal-body">
      <ModalContent text={props.text} dangerousHTML={props.dangerousHTML}>
        {props.children}
      </ModalContent>
      {props.showInput && <ModalInput {...props} />}
    </div>
  )
}

export default ModalBody
