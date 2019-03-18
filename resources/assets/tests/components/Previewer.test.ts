import Vue from 'vue'
import { mount } from '@vue/test-utils'
import Previewer from '@/components/Previewer.vue'
import * as emitter from '@/js/event'
import * as mockedSkinview3d from '../__mocks__/skinview3d'

type Viewer = Vue & { viewer: mockedSkinview3d.SkinViewer }

interface Handles {
  handles: {
    run: { paused: boolean }
    walk: { paused: boolean }
    rotate: { paused: boolean }
  }
}

test('initialize skinview3d', () => {
  const stub = jest.fn()
  emitter.on('skinViewerMounted', stub)

  const wrapper = mount<Viewer>(Previewer)
  expect(wrapper.vm.viewer).toBeInstanceOf(mockedSkinview3d.SkinViewer)
  expect(wrapper.vm.viewer.camera.position.z).toBe(70)
  expect(stub).toBeCalledWith(expect.any(HTMLElement))
})

test('dispose viewer before destroy', () => {
  const wrapper = mount<Viewer>(Previewer)
  wrapper.destroy()
  expect(wrapper.vm.viewer.disposed).toBeTrue()
})

test('skin URL should be updated', () => {
  const wrapper = mount<Viewer>(Previewer)
  wrapper.setProps({ skin: 'abc' })
  expect(wrapper.vm.viewer.skinUrl).toBe('abc')
})

test('cape URL should be updated', () => {
  const wrapper = mount<Viewer>(Previewer)
  wrapper.setProps({ cape: 'abc' })
  expect(wrapper.vm.viewer.capeUrl).toBe('abc')
})

test('`footer` slot', () => {
  const wrapper = mount(Previewer, {
    slots: {
      footer: '<div id="footer" />',
    },
  })
  expect(wrapper.find('#footer').exists()).toBeTrue()
})

test('disable closet mode', () => {
  const wrapper = mount(Previewer)
  expect(wrapper.find('.badge').text()).toBe('')
})

test('enable closet mode', () => {
  const wrapper = mount(Previewer, {
    propsData: {
      closetMode: true,
    },
  })
  expect(wrapper.find('.badge').text()).toBe('')

  wrapper.setProps({ skin: 'abc' })
  expect(wrapper.find('.badge').text()).toBe('general.skin')

  wrapper.setProps({ cape: 'abc', skin: '' })
  expect(wrapper.find('.badge').text()).toBe('general.cape')

  wrapper.setProps({ skin: 'abc', cape: 'abc' })
  expect(wrapper.find('.badge').text()).toBe('general.skin & general.cape')
})

test('toggle pause', () => {
  const wrapper = mount(Previewer)
  const pauseButton = wrapper.find('.fa-pause')
  expect(pauseButton.exists()).toBeTrue()
  pauseButton.trigger('click')
  expect(wrapper.find('.fa-play').exists()).toBeTrue()
  expect(wrapper.find('.fa-pause').exists()).toBeFalse()
})

test('toggle run', () => {
  const wrapper = mount<Vue & Handles>(Previewer)
  wrapper.find('.fa-forward').trigger('click')
  expect(wrapper.vm.handles.run.paused).toBeFalse()
  expect(wrapper.vm.handles.walk.paused).toBeTrue()
})

test('toggle rotate', () => {
  const wrapper = mount<Vue & Handles>(Previewer)
  wrapper.find('.fa-redo-alt').trigger('click')
  expect(wrapper.vm.handles.rotate.paused).toBeTrue()
})

test('reset', () => {
  mockedSkinview3d.SkinViewer.prototype.dispose = jest.fn(
    function (this: mockedSkinview3d.SkinViewer) {
      this.disposed = true
    }.bind(new mockedSkinview3d.SkinViewer())
  )
  const wrapper = mount(Previewer)
  wrapper.find('.fa-stop').trigger('click')
  expect(mockedSkinview3d.SkinViewer.prototype.dispose).toBeCalled()
})

test('custom title', () => {
  const wrapper = mount(Previewer, { propsData: { title: 'custom-title' } })
  expect(wrapper.text()).toContain('custom-title')
})
