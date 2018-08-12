import Vue from 'vue';
import { mount } from '@vue/test-utils';
import Register from '@/components/auth/Register';
import { swal } from '@/js/notify';

jest.mock('@/js/notify');

test('click to refresh captcha', () => {
    jest.spyOn(Date, 'now');
    const wrapper = mount(Register);
    wrapper.find('img').trigger('click');
    expect(Date.now).toHaveBeenCalledTimes(2);
});

test('register', async () => {
    jest.spyOn(Date, 'now');
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: 'fail' })
        .mockResolvedValueOnce({ errno: 0, msg: 'ok' });
    const wrapper = mount(Register);
    const button = wrapper.find('button');
    const info = wrapper.find('.callout-info');
    const warning = wrapper.find('.callout-warning');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.emptyEmail');

    wrapper.find('[type="email"]').setValue('a');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.invalidEmail');

    wrapper.find('[type="email"]').setValue('a@b.c');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.emptyPassword');

    wrapper.findAll('[type="password"]').at(0).setValue('123456');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.invalidPassword');

    wrapper.findAll('[type="password"]').at(0).setValue('12345678');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.invalidConfirmPwd');

    wrapper.findAll('[type="password"]').at(1).setValue('123456');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.invalidConfirmPwd');

    wrapper.findAll('[type="password"]').at(1).setValue('12345678');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.emptyNickname');

    wrapper.findAll('[type="text"]').at(0).setValue('abc');
    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();
    expect(info.text()).toBe('auth.emptyCaptcha');

    wrapper.findAll('[type="text"]').at(1).setValue('captcha');
    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/auth/register',
        {
            email: 'a@b.c',
            password: '12345678',
            nickname: 'abc',
            captcha: 'captcha'
        }
    );
    expect(warning.text()).toBe('fail');
    expect(Date.now).toHaveBeenCalledTimes(2);

    button.trigger('click');
    await wrapper.vm.$nextTick();
    jest.runAllTimers();
    expect(swal).toBeCalledWith({ type: 'success', html: 'ok' });
});
