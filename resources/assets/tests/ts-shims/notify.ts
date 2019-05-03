/* eslint-disable @typescript-eslint/indent */
import * as notify from '../../src/scripts/notify'

export const showAjaxError = {} as jest.Mock<
  ReturnType<typeof notify.showAjaxError>,
  Parameters<typeof notify.showAjaxError>
>

export const showModal = {} as jest.Mock<
  ReturnType<typeof notify.showModal>,
  Parameters<typeof notify.showModal>
>
