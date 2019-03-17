import $ from 'jquery'
import Swal from 'sweetalert2'
import * as notify from '@/js/notify'

jest.mock('sweetalert2', () => ({
  mixin() {
    return this
  },
  fire() {},
}))

test('show AJAX error', () => {
  $.fn.modal = function () {
    document.body.innerHTML = this.html()
  }
  notify.showAjaxError(new Error('an-error'))
  expect(document.body.innerHTML).toContain('an-error')
})

test('show modal', () => {
  notify.showModal('message')
  expect($('.modal-title').html()).toBe('Message')

  notify.showModal('message', '', 'default', {
    callback: () => undefined,
    destroyOnClose: false,
  })
})

test('show sweetalert', () => {
  jest.spyOn(Swal, 'fire')
  notify.swal({})
  expect(Swal.fire).toBeCalled()
})
