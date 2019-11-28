import { ModalOptions, ModalResult } from '../../src/scripts/notify'

export const showModal = {} as jest.Mock<Promise<ModalResult>, [ModalOptions | void]>
