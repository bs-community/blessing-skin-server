/* eslint-disable accessor-pairs */
import Vue from 'vue'
import { mount } from '@vue/test-utils'
import { Radio } from 'element-ui'
import * as skinview3d from 'skinview3d'
import Upload from '@/views/skinlib/Upload.vue'
import { flushPromises } from '../../utils'

window.blessing.extra = {
  textureNameRule: 'rule',
  privacyNotice: 'privacyNotice',
  scorePrivate: 10,
  scorePublic: 1,
  award: 0,
  contentPolicy: 'the policy',
}

const csrf = document.createElement('meta')
csrf.name = 'csrf-token'
csrf.content = 'token'
document.head.appendChild(csrf)

test('display drap and drop notice', () => {
  const wrapper = mount(Upload, {
    stubs: ['file-upload'],
  })
  expect(wrapper.text()).toContain('skinlib.upload.dropZone')
  wrapper.setData({ files: [{}] })
  expect(wrapper.contains('img')).toBeTrue()
})

test('button for removing texture', () => {
  const wrapper = mount<Vue & { texture: string }>(Upload, {
    stubs: ['file-upload'],
  })
  Object.defineProperty(wrapper.vm.$refs.upload, 'clear', {
    get: () => jest.fn(),
  })
  const button = wrapper.find('[data-test=remove]')
  expect(button.isVisible()).toBeFalse()
  wrapper.setData({ files: [{}] })
  expect(button.isVisible()).toBeTrue()
  button.trigger('click')
  expect(wrapper.vm.texture).toBe('')
})

test('notice should be display if texture is private', () => {
  const wrapper = mount(Upload, {
    stubs: ['file-upload'],
  })
  expect(wrapper.contains('.callout-info')).toBeFalse()
  wrapper.find('[type=checkbox]').setChecked()
  expect(wrapper.find('.callout-info').text()).toBe('privacyNotice')
})

test('display score cost', () => {
  const origin = Vue.prototype.$t
  Vue.prototype.$t = (key, args) => `${key}${JSON.stringify(args)}`

  const wrapper = mount(Upload, {
    stubs: ['file-upload'],
  })
  wrapper.find('[type=checkbox]').setChecked()
  wrapper.setData({ files: [{ size: 1024 }] })
  expect(wrapper.text()).toContain(JSON.stringify({ score: 10 }))

  Vue.prototype.$t = origin
})

test('process input file', () => {
  window.URL.createObjectURL = jest.fn().mockReturnValue('file-url')
  ;(window as Window & typeof globalThis & { Image: jest.Mock }).Image = jest.fn()
    .mockImplementationOnce(function (this: HTMLImageElement) {
      this.src = ''
      this.onload = null
      Object.defineProperty(this, 'onload', {
        set: fn => fn(),
      })
    })
    .mockImplementationOnce(function (this: HTMLImageElement) {
      this.src = ''
      this.width = 500
      this.onload = null
      Object.defineProperty(this, 'onload', {
        set: fn => fn(),
      })
    })
  jest.spyOn(skinview3d, 'isSlimSkin')
    .mockReturnValueOnce(true)
    .mockReturnValue(false)
  const blob = new Blob()
  type Component = Vue & {
    name: string
    texture: string
    inputFile(attrs?: { file: Blob, name: string }): void
  }
  const wrapper = mount<Component>(Upload, {
    stubs: ['file-upload'],
  })
  const radioes = wrapper.findAll(Radio)

  wrapper.vm.inputFile()
  expect(wrapper.vm.name).toBe('')

  wrapper.vm.inputFile({ file: blob, name: '123.png' })
  expect(wrapper.vm.name).toBe('123')
  expect(radioes.at(1).classes()).toContain('is-checked')

  wrapper.vm.inputFile({ file: blob, name: '456.png' })
  expect(wrapper.vm.name).toBe('123')
  expect(radioes.at(0).classes()).toContain('is-checked')

  wrapper.setData({ name: '' })
  wrapper.vm.inputFile({ file: blob, name: '789' })
  expect(wrapper.vm.name).toBe('789')

  expect(wrapper.vm.texture).toBe('file-url')

  ;(window as Window & typeof globalThis & { Image: jest.Mock }).Image.mockRestore()
})

test('upload file', async () => {
  (window as Window & typeof globalThis & { Request: jest.Mock }).Request = jest.fn()
  Vue.prototype.$http.post
    .mockResolvedValueOnce({ code: 1, message: '1' })
    .mockResolvedValueOnce({
      code: 0, message: '0', data: { tid: 1 },
    })

  const wrapper = mount(Upload, {
    stubs: ['file-upload'],
  })
  const button = wrapper.find('.btn-success')

  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(Vue.prototype.$message.error).toBeCalledWith('skinlib.emptyUploadFile')

  wrapper.setData({
    files: [{
      file: {}, name: 't', type: 'image/jpeg',
    }],
  })
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(Vue.prototype.$message.error).toBeCalledWith('skinlib.emptyTextureName')

  wrapper.find('[type=text]').setValue('t')
  button.trigger('click')
  expect(Vue.prototype.$http.post).not.toBeCalled()
  expect(Vue.prototype.$message.error).toBeCalledWith('skinlib.fileExtError')

  wrapper.setData({
    files: [{
      file: new Blob(), name: 't.png', type: 'image/png',
    }],
  })
  button.trigger('click')
  await flushPromises()
  expect(Vue.prototype.$http.post).toBeCalledWith('/skinlib/upload', expect.any(FormData))
  expect(Vue.prototype.$message.error).toBeCalledWith('1')

  button.trigger('click')
  await flushPromises()
  jest.runAllTimers()
  expect(Vue.prototype.$message.success).toBeCalledWith('0')
})

test('show notice about awarding', () => {
  const wrapper = mount(Upload)
  expect(wrapper.find('.callout-success').exists()).toBeFalse()

  wrapper.setData({ award: 5 })
  expect(wrapper.find('.callout-success').exists()).toBeTrue()

  wrapper.find('[type=checkbox]').setChecked()
  expect(wrapper.find('.callout-success').exists()).toBeFalse()
})

test('show content policy', () => {
  const wrapper = mount(Upload)
  expect(wrapper.find('.callout-warning').exists()).toBeTrue()
})
