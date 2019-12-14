import { mount } from '@vue/test-utils'
import Portal from '@/components/Portal'

beforeEach(() => {
  document.body.innerHTML = ''
})

test('render default slot', () => {
  mount(Portal, {
    propsData: {
      selector: 'body',
    },
    slots: {
      default: 'children',
    },
  })
  expect(document.querySelectorAll('div')).toHaveLength(1)
  expect(document.querySelector('div')!.textContent).toBe('children')
})

test('custom wrapper tag', () => {
  mount(Portal, {
    propsData: {
      selector: 'body',
      tag: 'span',
    },
  })
  expect(document.querySelectorAll('span')).toHaveLength(1)
})

test('should pass if container does not exist', () => {
  mount(Portal, {
    propsData: {
      selector: '#nope',
    },
  })
})

test('replace container content', () => {
  document.body.innerHTML = '<div>before</div>'
  mount(Portal, {
    propsData: {
      selector: 'body',
    },
    slots: {
      default: 'after',
    },
  })
  expect(document.querySelector('div')!.textContent).toBe('after')
})
