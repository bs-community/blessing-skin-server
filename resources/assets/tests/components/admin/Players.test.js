import Vue from 'vue';
import { mount } from '@vue/test-utils';
import { flushPromises } from '../../utils';
import Players from '@/components/admin/Players';
import { swal } from '@/js/notify';

jest.mock('@/js/notify');

test('fetch data after initializing', () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [] });
    mount(Players);
    expect(Vue.prototype.$http.get).toBeCalledWith('/admin/player-data');
});

test('change texture', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [
        { pid: 1, tid_steve: 0 }
    ] });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValueOnce({ errno: 0, msg: '0' });
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({ value: 5 });

    const wrapper = mount(Players);
    await wrapper.vm.$nextTick();
    const button = wrapper.find('[data-test="change-texture"] > li:nth-child(1) > a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/players?action=texture',
        { pid: 1, model: 'steve', tid: 5 }
    );

    button.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('5');
});

test('change player name', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [
        { pid: 1, player_name: 'old' }
    ] });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValueOnce({ errno: 0, msg: '0' });
    swal.mockImplementationOnce(() => ({ dismiss: 1 }))
        .mockImplementation(options => {
            options.inputValidator();
            options.inputValidator('new');
            return { value: 'new' };
        });

    const wrapper = mount(Players);
    await wrapper.vm.$nextTick();
    const button = wrapper.find('[data-test="operations"] > li:nth-child(1) > a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/players?action=name',
        { pid: 1, name: 'new' }
    );

    button.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('new');
});

test('toggle preference', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [
        { pid: 1, preference: 'default' }
    ] });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValue({ errno: 0, msg: '0' });

    const wrapper = mount(Players);
    await wrapper.vm.$nextTick();
    const button = wrapper.find('[data-test="operations"] > li:nth-child(2) > a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/players?action=preference',
        { pid: 1, preference: 'slim' }
    );

    button.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('slim');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('default');
});

test('change owner', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [
        { pid: 1, uid: 2 }
    ] });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValueOnce({ errno: 0, msg: '0' });
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({ value: '3' });

    const wrapper = mount(Players);
    await wrapper.vm.$nextTick();
    const button = wrapper.find('[data-test="operations"] > li:nth-child(3) > a');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/players?action=owner',
        { pid: 1, uid: '3' }
    );

    button.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('3');
});

test('delete player', async () => {
    Vue.prototype.$http.get.mockResolvedValue({ data: [
        { pid: 1, player_name: 'to-be-deleted' }
    ] });
    Vue.prototype.$http.post
        .mockResolvedValueOnce({ errno: 1, msg: '1' })
        .mockResolvedValueOnce({ errno: 0, msg: '0' });
    swal.mockResolvedValueOnce({ dismiss: 1 })
        .mockResolvedValue({});

    const wrapper = mount(Players);
    await wrapper.vm.$nextTick();
    const button = wrapper.find('.btn-danger');

    button.trigger('click');
    expect(Vue.prototype.$http.post).not.toBeCalled();

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/players?action=delete',
        { pid: 1 }
    );
    expect(wrapper.text()).toContain('to-be-deleted');

    button.trigger('click');
    await flushPromises();
    expect(wrapper.vm.players).toHaveLength(0);
});
