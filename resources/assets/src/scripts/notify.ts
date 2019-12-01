import { showModal } from './modal'
import { Toast } from './toast'

export const toast = new Toast()
Object.assign(blessing, { notify: { showModal, toast } })

export { showModal } from './modal'
