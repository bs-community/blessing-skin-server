import { mount } from '@vue/test-utils';
import ClosetItem from '@/user/ClosetItem';
import axios from 'axios';
import swal from 'sweetalert2';

jest.mock('axios');
jest.mock('sweetalert2');

window.blessing = {
    base_url: ''
};

function factory(opt = {}) {
    return {
        tid: 1,
        name: 'texture',
        type: 'steve',
        ...opt
    };
}

beforeEach(() => {
    axios.post.mockReset();
    swal.mockReset();
});

test('computed values', () => {
    const wrapper = mount(ClosetItem, { propsData: factory() });
    expect(wrapper.find('img').attributes().src).toBe('/preview/1.png');
    expect(wrapper.find('a.more').attributes().href).toBe('/skinlib/show/1');
});

test('selected item', () => {
    const wrapper = mount(ClosetItem, { propsData: factory({ selected: true }) });
    expect(wrapper.find('.item').classes()).toContain('item-selected');
});

test('click item body', () => {
    const wrapper = mount(ClosetItem, { propsData: factory() });

    wrapper.find('.item').trigger('click');
    expect(wrapper.emitted().select).toBeUndefined();

    wrapper.find('.item-body').trigger('click');
    expect(wrapper.emitted().select).toBeTruthy();
});

test('rename texture', async () => {
    axios.post
        .mockResolvedValueOnce({ data: { errno: 0 } })
        .mockResolvedValueOnce({ data: { errno: 1 } });
    swal.mockImplementation(async options => {
        options.inputValidator('name');
        options.inputValidator().catch(() => {});
        return 'new-name';
    });

    const wrapper = mount(ClosetItem, { propsData: factory() });
    const button = wrapper.findAll('.dropdown-menu > li').at(0).find('a');

    button.trigger('click');
    await wrapper.vm.$nextTick();

    button.trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.texture-name > span').text()).toBe('new-name (steve)');
    expect(axios.post).toBeCalledWith(
        '/user/closet/rename',
        { tid: 1, new_name: 'new-name' }
    );
});

test('remove texture', async () => {
    axios.post
        .mockResolvedValueOnce({ data: { errno: 0 } })
        .mockResolvedValueOnce({ data: { errno: 1 } });
    swal.mockResolvedValue();

    const wrapper = mount(ClosetItem, { propsData: factory() });
    const button = wrapper.findAll('.dropdown-menu > li').at(1).find('a');

    button.trigger('click');
    await wrapper.vm.$nextTick();

    button.trigger('click');
    await wrapper.vm.$nextTick();

    await wrapper.vm.$nextTick();
    expect(wrapper.emitted()['item-removed'][0][0]).toBe(1);
    expect(axios.post).toBeCalledWith('/user/closet/remove', { tid: 1 });
});

test('set as avatar', async () => {
    axios.post
        .mockResolvedValueOnce({ data: { errno: 0 } })
        .mockResolvedValueOnce({ data: { errno: 1 } });
    swal.mockResolvedValue();
    window.$ = jest.fn(() => ({
        each(fn) { fn(); },
        prop() {},
        attr() { return ''; }
    }));

    const wrapper = mount(ClosetItem, { propsData: factory() });
    const button = wrapper.findAll('.dropdown-menu > li').at(2).find('a');

    button.trigger('click');
    await wrapper.vm.$nextTick();

    button.trigger('click');
    await wrapper.vm.$nextTick();

    await wrapper.vm.$nextTick();
    expect(axios.post).toBeCalledWith('/user/profile/avatar', { tid: 1 });
    expect(window.$).toBeCalledWith('[alt="User Image"]');
});
