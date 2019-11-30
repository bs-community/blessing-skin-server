import $ from 'jquery'
import 'bootstrap'
import { mount } from '@vue/test-utils'
import Modal from '@/components/Modal.vue'

test('id', () => {
  const wrapper = mount(Modal, {
    propsData: {
      id: 'id',
    },
  })
  expect(wrapper.find('#id').exists()).toBeTrue()
})

test('title', () => {
  const wrapper = mount(Modal, {
    propsData: {
      title: 'kumiko',
    },
  })
  expect(wrapper.find('.modal-title').text()).toBe('kumiko')
})

test('close button at header', () => {
  const wrapper = mount(Modal)
  wrapper.find('.modal-header > button').trigger('click')
  expect(wrapper.emitted().dismiss).toHaveLength(1)
})

test('render lines', () => {
  const wrapper = mount(Modal, {
    propsData: {
      text: 'kumiko\nreina',
    },
  })
  const paragraphs = wrapper.findAll('p')
  expect(paragraphs).toHaveLength(2)
  expect(paragraphs.at(0).text()).toBe('kumiko')
  expect(paragraphs.at(1).text()).toBe('reina')
})

test('dynamic html', () => {
  const wrapper = mount(Modal, {
    propsData: {
      dangerousHTML: '<div class="eupho">kumiko</div>',
    },
  })
  expect(wrapper.find('.eupho').text()).toBe('kumiko')
})

test('cancel by button at footer', () => {
  const wrapper = mount(Modal)
  wrapper.find('.btn-secondary').trigger('click')
  expect(wrapper.emitted().dismiss).toHaveLength(1)
})

test('alert mode', () => {
  const wrapper = mount(Modal, {
    propsData: {
      mode: 'alert',
    },
  })
  expect(wrapper.find('.btn-secondary').exists()).toBeFalse()
})

test('prompt mode', () => {
  const wrapper = mount(Modal, {
    propsData: {
      mode: 'prompt',
      input: 'default-value',
    },
  })
  expect(wrapper.find('.btn-secondary').exists()).toBeTrue()

  wrapper.find('input').setValue('hazuki')
  wrapper.find('.btn-primary').trigger('click')
  expect(wrapper.emitted().confirm[0][0]).toStrictEqual({ value: 'hazuki' })
})

test('input placeholder', () => {
  const wrapper = mount(Modal, {
    propsData: {
      mode: 'prompt',
      placeholder: 'hibike',
    },
  })
  expect(wrapper.find('input').attributes('placeholder')).toBe('hibike')
})

test('validate input', () => {
  const stub = jest.fn()
    .mockReturnValueOnce(false)
    .mockReturnValueOnce('invalid')

  const wrapper = mount(Modal, {
    propsData: {
      mode: 'prompt',
      validator: stub,
    },
  })
  const button = wrapper.find('.btn-primary')

  button.trigger('click')
  expect(wrapper.find('.alert').exists()).toBeFalse()

  button.trigger('click')
  expect(wrapper.find('.alert').text()).toContain('invalid')
})

test('input type', () => {
  const wrapper = mount(Modal, {
    propsData: {
      mode: 'prompt',
      inputType: 'password',
    },
  })
  expect(wrapper.find('[type=password]').exists()).toBeTrue()
})

test('modal type', () => {
  const wrapper = mount(Modal, {
    propsData: {
      type: 'danger',
    },
  })
  expect(wrapper.find('.modal-content').classes('bg-danger')).toBeTrue()
})

test('hide header', () => {
  const wrapper = mount(Modal, {
    propsData: {
      showHeader: false,
    },
  })
  expect(wrapper.find('.modal-header').exists()).toBeFalse()
})

test('centered modal', () => {
  const wrapper = mount(Modal, {
    propsData: {
      center: true,
    },
  })
  expect(
    wrapper.find('.modal-dialog').classes('modal-dialog-centered'),
  ).toBeTrue()
})

test('customize ok button', () => {
  const wrapper = mount(Modal, {
    propsData: {
      okButtonText: 'OK',
      okButtonType: 'danger',
    },
  })
  const button = wrapper.find('.btn:nth-child(2)')
  expect(button.text().trim()).toBe('OK')
  expect(button.classes('btn-danger')).toBeTrue()
})

test('customize cancel button', () => {
  const wrapper = mount(Modal, {
    propsData: {
      cancelButtonText: 'CANCEL',
      cancelButtonType: 'danger',
    },
  })
  const button = wrapper.find('.btn:nth-child(1)')
  expect(button.text().trim()).toBe('CANCEL')
  expect(button.classes('btn-danger')).toBeTrue()
})

test('flex footer', () => {
  const wrapper = mount(Modal, {
    propsData: {
      flexFooter: true,
    },
  })
  expect(wrapper.find('.modal-footer').classes())
    .toContainValues(['d-flex', 'justify-content-between'])
})

test('default slot', () => {
  const wrapper = mount(Modal, {
    slots: {
      default: '<div class="trumpet">reina</div>',
    },
  })
  expect(wrapper.find('.modal-body > .trumpet').text()).toBe('reina')
})

test('footer slot', () => {
  const wrapper = mount(Modal, {
    slots: {
      footer: '<div class="contrabass">sapphire</div>',
    },
  })
  expect(wrapper.find('.modal-footer > .contrabass').text()).toBe('sapphire')
})

test('prevent duplicated dismission', () => {
  const wrapper = mount(Modal)
  wrapper.find('.btn-secondary').trigger('click')
  $(wrapper.element).trigger('hide.bs.modal')
  $(wrapper.element).trigger('hidden.bs.modal')
  expect(wrapper.emitted().dismiss).toHaveLength(1)

  wrapper.find('.btn-primary').trigger('click')
  $(wrapper.element).trigger('hide.bs.modal')
  $(wrapper.element).trigger('hidden.bs.modal')
  expect(wrapper.emitted().dismiss).toHaveLength(1)

  $(wrapper.element).trigger('hide.bs.modal')
  $(wrapper.element).trigger('hidden.bs.modal')
  expect(wrapper.emitted().dismiss).toHaveLength(2)
})
