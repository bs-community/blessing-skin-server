import Vue from 'vue';
import { mount } from '@vue/test-utils';
import SkinLibItem from '@/components/skinlib/SkinLibItem';
import { flushPromises } from '../../utils';
import { swal } from '@/js/notify';
import toastr from 'toastr';

jest.mock('@/js/notify');
jest.mock('toastr');

test('urls', () => {
    const wrapper = mount(SkinLibItem, {
        propsData: { tid: 1 }
    });
    expect(wrapper.find('a').attributes('href')).toBe('/skinlib/show/1');
    expect(wrapper.find('img').attributes('src')).toBe('/preview/1.png');
});

test('render basic information', () => {
    const wrapper = mount(SkinLibItem, {
        propsData: {
            tid: 1,
            name: 'test',
            type: 'steve',
        }
    });
    expect(wrapper.text()).toContain('test');
    expect(wrapper.text()).toContain('skinlib.filter.steve');
});

test('anonymous user', () => {
    const wrapper = mount(SkinLibItem, {
        propsData: { anonymous: true }
    });
    const button = wrapper.find('.more');
    expect(button.attributes('title')).toBe('skinlib.anonymous');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
});

test('private texture', () => {
    const wrapper = mount(SkinLibItem, {
        propsData: { isPublic: false }
    });
    expect(wrapper.text()).toContain('skinlib.private');

    wrapper.setProps({ isPublic: true });
    expect(wrapper.text()).not.toContain('skinlib.private');
});

test('liked state', () => {
    const wrapper = mount(SkinLibItem, {
        propsData: { liked: true, anonymous: false }
    });
    const button = wrapper.find('.like');

    expect(button.attributes('title')).toBe('skinlib.removeFromCloset');
    expect(button.classes('liked')).toBeTrue();

    wrapper.setProps({ liked: false });
    expect(button.attributes('title')).toBe('skinlib.addToCloset');
    expect(button.classes('liked')).toBeFalse();
});

test('remove from closet', async () => {
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0 });
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    jest.spyOn(toastr, 'warning');
    const wrapper = mount(SkinLibItem, {
        propsData: { tid: 1, liked: true, anonymous: false }
    });
    const button = wrapper.find('.like');

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
    expect(wrapper.emitted('like-toggled')[0]).toEqual([false]);
});

test('add to closet', async () => {
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0 });
    swal.mockImplementationOnce(() => ({ dismiss: 1 }))
        .mockImplementation(({ inputValidator }) => {
            if (inputValidator) {
                inputValidator();
                inputValidator('name');
            }
            return { value: 'name' };
        });
    jest.spyOn(toastr, 'warning');
    const wrapper = mount(SkinLibItem, {
        propsData: { tid: 1, liked: false, anonymous: false }
    });
    const button = wrapper.find('.like');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await flushPromises();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/closet/add',
        { tid: 1, name: 'name' }
    );
    expect(toastr.warning).toBeCalledWith('1');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.emitted('like-toggled')[0]).toEqual([true]);
});
