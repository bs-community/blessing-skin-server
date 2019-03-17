import * as notify from '../../src/js/notify'

export const showAjaxError = {} as jest.Mock<
  ReturnType<typeof notify.showAjaxError>,
  Parameters<typeof notify.showAjaxError>
>

export const showModal = {} as jest.Mock<
  ReturnType<typeof notify.showModal>,
  Parameters<typeof notify.showModal>
>

export const swal = {} as jest.Mock<
  ReturnType<typeof notify.swal>,
  Parameters<typeof notify.swal>
>
