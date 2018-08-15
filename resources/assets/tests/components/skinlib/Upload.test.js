import Vue from 'vue';
import { mount } from '@vue/test-utils';
import Upload from '@/components/skinlib/Upload';
import { flushPromises } from '../../utils';
import toastr from 'toastr';
import { swal } from '@/js/notify';
import { walkFetch } from '@/js/net';

jest.mock('toastr');
jest.mock('@/js/notify');
jest.mock('@/js/net');

window.__bs_data__ = {
    textureNameRule: 'rule',
    privacyNotice: 'privacyNotice',
    scorePrivate: 10,
    scorePublic: 1
};

test('display drap and drop notice', () => {
    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });
    expect(wrapper.text()).toContain('skinlib.upload.dropZone');
    wrapper.setData({ files: [{}] });
    expect(wrapper.contains('img')).toBeTrue();
});

test('button for removing texture', () => {
    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });
    wrapper.vm.$refs = {
        upload: {
            clear: jest.fn()
        }
    };
    const button = wrapper.find('.btn-default');
    expect(button.isVisible()).toBeFalse();
    wrapper.setData({ files: [{}] });
    expect(button.isVisible()).toBeTrue();
    button.trigger('click');
    expect(wrapper.vm.texture).toBe('');
});

test('notice should be display if texture is private', () => {
    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });
    expect(wrapper.contains('.callout')).toBeFalse();
    wrapper.find('[type=checkbox]').setChecked();
    expect(wrapper.find('.callout').text()).toBe('privacyNotice');
});

test('display score cost', () => {
    const origin = Vue.prototype.$t;
    Vue.prototype.$t = (key, args) => `${key}${JSON.stringify(args)}`;

    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });
    wrapper.find('[type=checkbox]').setChecked();
    wrapper.setData({ files: [{ size: 1024 }] });
    expect(wrapper.text()).toContain(JSON.stringify({ score: 10 }));

    Vue.prototype.$t = origin;
});

test('process input file', () => {
    window.URL.createObjectURL = jest.fn().mockReturnValue('file-url');
    const blob = new Blob;
    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });

    wrapper.vm.inputFile();
    expect(wrapper.vm.name).toBe('');

    wrapper.vm.inputFile({ file: blob, name: '123.png' });
    expect(wrapper.vm.name).toBe('123');

    wrapper.vm.inputFile({ file: blob, name: '456.png' });
    expect(wrapper.vm.name).toBe('123');

    wrapper.setData({ name: '' });
    wrapper.vm.inputFile({ file: blob, name: '789' });
    expect(wrapper.vm.name).toBe('789');

    expect(wrapper.vm.texture).toBe('file-url');
});

test('upload file', async () => {
    window.Request = jest.fn();
    walkFetch
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValueOnce({ errno: 0, msg: '0', tid: 1 });
    jest.spyOn(toastr, 'info');
    swal.mockReturnValue();

    const wrapper = mount(Upload, {
        stubs: ['file-upload']
    });
    const button = wrapper.find('.box-footer > button');

    button.trigger('click');
    expect(walkFetch).not.toBeCalled();
    expect(toastr.info).toBeCalledWith('skinlib.emptyUploadFile');

    wrapper.setData({ files: [{ file: {}, name: 't', type: 'image/jpeg' }] });
    button.trigger('click');
    expect(walkFetch).not.toBeCalled();
    expect(toastr.info).toBeCalledWith('skinlib.emptyTextureName');

    wrapper.find('[type=text]').setValue('t');
    button.trigger('click');
    expect(walkFetch).not.toBeCalled();
    expect(toastr.info).toBeCalledWith('skinlib.fileExtError');

    wrapper.setData({ files: [{ file: new Blob, name: 't.png', type: 'image/png' }] });
    button.trigger('click');
    await flushPromises();
    expect(window.Request).toBeCalledWith('/skinlib/upload', {
        body: expect.any(FormData),
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json'
        },
        method: 'POST'
    });
    expect(walkFetch).toBeCalledWith(expect.any(window.Request));
    expect(swal).toBeCalledWith({ type: 'warning', text: '1' });

    button.trigger('click');
    await flushPromises();
    jest.runAllTimers();
    expect(swal).toBeCalledWith({ type: 'success', text: '0' });
    expect(toastr.info).toBeCalledWith('skinlib.redirecting');
});
