import Vue from 'vue';
import { mount } from '@vue/test-utils';
import { flushPromises } from '../../utils';
import Profile from '@/components/user/Profile';
import toastr from 'toastr';
import { swal } from '@/js/notify';

jest.mock('@/js/notify');

test('computed values', () => {
    window.__bs_data__ = { admin: true };
    const wrapper = mount(Profile);
    expect(wrapper.vm.siteName).toBe('Blessing Skin');
    expect(wrapper.vm.isAdmin).toBeTrue();
    window.__bs_data__ = { admin: false };
    expect(mount(Profile).vm.isAdmin).toBeFalse();
});

test('convert linebreak', () => {
    const wrapper = mount(Profile);
    expect(wrapper.vm.nl2br('a\nb\nc')).toBe('a<br>b<br>c');
});

test('change password', async () => {
    jest.spyOn(toastr, 'info');
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: 'w' })
        .mockResolvedValueOnce({ errno: 0, msg: 'o' });
    swal.mockResolvedValue();
    const wrapper = mount(Profile);
    const button = wrapper.find('[data-test=changePassword]');

    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptyPassword');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ oldPassword: '1' });
    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptyNewPassword');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ newPassword: '1' });
    button.trigger('click');
    expect(toastr.info).toBeCalledWith('auth.emptyConfirmPwd');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ confirmPassword: '2' });
    button.trigger('click');
    expect(toastr.info).toBeCalledWith('auth.invalidConfirmPwd');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ confirmPassword: '1' });
    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/profile?action=password',
        { current_password: '1', new_password: '1' }
    );
    expect(swal).toBeCalledWith({ type: 'warning', text: 'w' });

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'success', text: 'o' });
});

test('change nickname', async () => {
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: 'w' })
        .mockResolvedValue({ errno: 0, msg: 'o' });
    swal.mockResolvedValueOnce({})
        .mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    window.$ = jest.fn(() => ({
        each(fn) {
            fn();
        },
        text() {}
    }));
    const wrapper = mount(Profile);
    const button = wrapper.find('[data-test=changeNickName]');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(swal).toBeCalledWith({ type: 'error', text: 'user.emptyNewNickName' });

    wrapper.setData({ nickname: 'nickname' });
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(swal).toBeCalledWith({
        text: 'user.changeNickName',
        type: 'question',
        showCancelButton: true
    });

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/profile?action=nickname',
        { new_nickname: 'nickname' }
    );
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'warning', text: 'w' });

    button.trigger('click');
    await flushPromises();
    expect(swal).toBeCalledWith({ type: 'success', text: 'o' });
});

test('change email', async () => {
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: 'w' })
        .mockResolvedValue({ errno: 0, msg: 'o' });
    swal.mockResolvedValueOnce({})
        .mockResolvedValueOnce({})
        .mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});
    const wrapper = mount(Profile);
    const button = wrapper.find('[data-test=changeEmail]');

    button.trigger('click');
    expect(swal).toBeCalledWith({ type: 'error', text: 'user.emptyNewEmail' });
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ email: 'e' });
    button.trigger('click');
    expect(swal).toBeCalledWith({ type: 'warning', text: 'auth.invalidEmail' });
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ email: 'a@b.c', currentPassword: 'abc' });
    button.trigger('click');
    expect(swal).toBeCalledWith({
        text: 'user.changeEmail',
        type: 'question',
        showCancelButton: true
    });
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/profile?action=email',
        { new_email: 'a@b.c', password: 'abc' }
    );
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'warning', text: 'w' });

    button.trigger('click');
    await flushPromises();
    expect(swal).toBeCalledWith({ type: 'success', text: 'o' });
});

test('delete account', async () => {
    window.__bs_data__ = { admin: true };
    swal.mockResolvedValue();
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: 'w' })
        .mockResolvedValue({ errno: 0, msg: 'o' });
    const wrapper = mount(Profile);
    const button = wrapper.find('[data-test=deleteAccount]');

    button.trigger('click');
    expect(swal).toBeCalledWith({ type: 'warning', text: 'user.emptyDeletePassword' });
    expect(Vue.prototype.$http.post).not.toBeCalled();

    wrapper.setData({ deleteConfirm: 'abc' });
    button.trigger('click');
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/user/profile?action=delete',
        { password: 'abc' }
    );
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'warning', text: 'w' });

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'success', text: 'o' });
});
