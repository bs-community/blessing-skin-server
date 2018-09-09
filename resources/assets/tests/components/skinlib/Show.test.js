import Vue from 'vue';
import { mount } from '@vue/test-utils';
import Show from '@/components/skinlib/Show';
import { flushPromises } from '../../utils';
import { swal } from '@/js/notify';
import toastr from 'toastr';

jest.mock('@/js/notify');

window.blessing.extra = {
    download: true,
    currentUid: 0,
    admin: false,
    nickname: 'author',
    inCloset: false,
};

/** @type {import('Vue').ComponentOptions} */
const previewer = {
    render(h) {
        return h('div', this.$slots.footer);
    },
};

test('button for adding to closet should be disabled if not auth', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    expect(wrapper.find('.btn-primary').attributes('disabled')).toBe('disabled');
});

test('button for adding to closet should be disabled if auth', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    Object.assign(window.blessing.extra, { inCloset: true, currentUid: 1 });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    expect(wrapper.find('.btn-primary').text()).toBe('skinlib.removeFromCloset');
});

test('likes count indicator', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ likes: 2 });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.find('.likes').attributes('style')).toContain('color: rgb(224, 53, 59)');
    expect(wrapper.find('.likes').text()).toContain('2');
});

test('render basic information', async () => {
    Vue.prototype.$http.get.mockResolvedValue({
        name: 'my-texture',
        type: 'alex',
        hash: '123',
        size: 2,
        upload_at: '2018'
    });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    await wrapper.vm.$nextTick();
    const text = wrapper.find('.box-primary').text();
    expect(text).toContain('my-texture');
    expect(text).toContain('alex');
    expect(text).toContain('123...');
    expect(text).toContain('2 KB');
    expect(text).toContain('2018');
    expect(text).toContain('author');
});

test('render action text of editing texture name', async () => {
    Object.assign(window.blessing.extra, { admin: true });
    Vue.prototype.$http.get.mockResolvedValue({ uploader: 1, name: 'name' });

    let wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.contains('small')).toBeTrue();

    Object.assign(window.blessing.extra, { currentUid: 2, admin: false });
    wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.contains('small')).toBeFalse();
});

test('render nickname of uploader', () => {
    Object.assign(window.blessing.extra, { nickname: null });
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    expect(wrapper.text()).toContain('general.unexistent-user');
});

test('operation panel should not be rendered if not auth', () => {
    Object.assign(window.blessing.extra, { currentUid: 0 });
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    expect(wrapper.contains('.box-warning')).toBeFalse();
});

test('link to downloading texture', async () => {
    Object.assign(window.blessing.extra, { download: false });
    Vue.prototype.$http.get.mockResolvedValue({ hash: '123' });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        }
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.contains('a[title="123"]')).toBeFalse();
    expect(wrapper.contains('span[title="123"]')).toBeTrue();
});

test('add to closet', async () => {
    Object.assign(window.blessing.extra, { currentUid: 1, inCloset: false });
    Vue.prototype.$http.get.mockResolvedValue({ name: 'wow', likes: 2 });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '' });
    jest.spyOn(toastr, 'warning');
    swal.mockImplementationOnce(() => ({ dismiss: 1 }))
        .mockImplementation(({ inputValidator }) => {
            if (inputValidator) {
                inputValidator();
                inputValidator('wow');
            }
            return { value: 'wow' };
        });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.find('.btn-primary');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/closet/add',
        { tid: 1, name: 'wow' }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.likes).toBe(3);
    expect(wrapper.vm.liked).toBeTrue();
});

test('remove from closet', async () => {
    Object.assign(window.blessing.extra, { currentUid: 1, inCloset: true });
    Vue.prototype.$http.get.mockResolvedValue({ likes: 2 });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '' });
    jest.spyOn(toastr, 'warning');
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.find('.btn-primary');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/closet/remove',
        { tid: 1 }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.likes).toBe(1);
    expect(wrapper.vm.liked).toBeFalse();
});

test('change texture name', async () => {
    Object.assign(window.blessing.extra, { admin: true });
    Vue.prototype.$http.get.mockResolvedValue({ name: 'old-name' });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '0' });
    jest.spyOn(toastr, 'warning');
    swal.mockImplementationOnce(() => ({ dismiss: 1 }))
        .mockImplementation(({ inputValidator }) => {
            inputValidator();
            inputValidator('new-name');
            return { value: 'new-name' };
        });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.find('small > a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/skinlib/rename',
        { tid: 1, new_name: 'new-name' }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.name).toBe('new-name');
});

test('change texture model', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ type: 'steve' });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '0' });
    jest.spyOn(toastr, 'warning');
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({ value: 'alex' });
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.findAll('small').at(1).find('a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/skinlib/model',
        { tid: 1, model: 'alex' }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.type).toBe('alex');
});

test('toggle privacy', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ public: true });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '0' });
    jest.spyOn(toastr, 'warning');
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.find('.btn-warning');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/skinlib/privacy',
        { tid: 1 }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.public).toBeFalse();

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.public).toBeTrue();
});

test('delete texture', async () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '0' });
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    const wrapper = mount(Show, {
        mocks: {
            $route: ['/skinlib/show/1', '1']
        },
        stubs: { previewer }
    });
    const button = wrapper.find('.btn-danger');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/skinlib/delete',
        { tid: 1 }
    );
    expect(swal).toBeCalledWith({ type: 'warning', text: '1' });

    button.trigger('click');
    await flushPromises();
    expect(swal).toBeCalledWith({ type: 'success', text: '0' });
});
