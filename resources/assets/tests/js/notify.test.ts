import $ from 'jquery'
import * as notify from '@/js/notify'

test('show AJAX error', () => {
  // @ts-ignore
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
