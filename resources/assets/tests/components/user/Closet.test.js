import { mount } from '@vue/test-utils';
import Closet from '@/components/user/Closet';
import ClosetItem from '@/components/user/ClosetItem';
import Previewer from '@/components/common/Previewer';
import axios from 'axios';
import toastr from 'toastr';
import { swal } from '@/js/notify';

jest.mock('axios');
jest.mock('@/js/notify');

test('fetch closet data before mount', () => {
    axios.mockResolvedValue({ data: {} });
    mount(Closet);
    jest.runAllTicks();
    expect(axios).toBeCalledWith({
        method: 'GET',
        url: '/user/closet-data',
        params: {
            category: 'skin',
            q: '',
            page: 1,
        }
    });
});

test('switch tabs', () => {
    axios.mockResolvedValue({
        data: {
            items: [],
            category: 'skin',
            total_pages: 1
        }
    }).mockResolvedValueOnce({
        data: {
            items: [],
            category: 'cape',
            total_pages: 1
        }
    });

    const wrapper = mount(Closet);

    const tabSkin = wrapper.findAll('.nav-tabs > li').at(0);
    tabSkin.find('a').trigger('click');
    jest.runAllTicks();
    expect(axios).toBeCalledWith({
        method: 'GET',
        url: '/user/closet-data',
        params: {
            category: 'skin',
            q: '',
            page: 1,
        }
    });

    const tabCape = wrapper.findAll('.nav-tabs > li').at(1);
    tabCape.find('a').trigger('click');
    jest.runAllTicks();
    expect(axios).toBeCalledWith({
        method: 'GET',
        url: '/user/closet-data',
        params: {
            category: 'cape',
            q: '',
            page: 1,
        }
    });
});

test('different categories', () => {
    axios.mockResolvedValue({ data: {} });

    const wrapper = mount(Closet);
    expect(wrapper.findAll('.nav-tabs > li').at(0).classes()).toContain('active');
    expect(wrapper.find('#skin-category').classes()).toContain('active');

    wrapper.setData({ category: 'cape' });
    expect(wrapper.findAll('.nav-tabs > li').at(1).classes()).toContain('active');
    expect(wrapper.find('#cape-category').classes()).toContain('active');
});

test('search textures', () => {
    jest.useFakeTimers();
    axios.mockResolvedValue({ data: {} });

    const wrapper = mount(Closet);
    const input = wrapper.find('input');
    input.element.value = 'q';
    input.trigger('input');
    jest.runAllTimers();
    jest.runAllTicks();
    expect(axios).toBeCalledWith({
        method: 'GET',
        url: '/user/closet-data',
        params: {
            category: 'skin',
            q: 'q',
            page: 1,
        }
    });

    jest.useRealTimers();
});

test('empty closet', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg');
    wrapper.setData({ category: 'cape' });
    expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg');
});

test('no matched search result', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    wrapper.setData({ query: 'q' });
    expect(wrapper.find('#skin-category').text()).toContain('general.noResult');
    wrapper.setData({ category: 'cape' });
    expect(wrapper.find('#cape-category').text()).toContain('general.noResult');
});

test('render items', async () => {
    axios.mockResolvedValue({ data: {
        items: [
            { tid: 1 },
            { tid: 2 }
        ],
        category: 'skin',
        total_pages: 1
    } });
    const wrapper = mount(Closet);
    await wrapper.vm.$nextTick();
    expect(wrapper.findAll(ClosetItem)).toHaveLength(2);
});

test('reload closet when page changed', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    wrapper.vm.pageChanged();
    jest.runAllTicks();
    expect(axios).toHaveBeenCalledTimes(2);
});

test('remove skin item', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    wrapper.setData({ skinItems: [{ tid: 1 }, { tid: 2 }] });
    wrapper.vm.removeSkinItem(1);
    expect(wrapper.findAll(ClosetItem)).toHaveLength(1);
});

test('remove cape item', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    wrapper.setData({ capeItems: [{ tid: 1 }, { tid: 2 }], category: 'cape' });
    wrapper.vm.removeCapeItem(1);
    expect(wrapper.findAll(ClosetItem)).toHaveLength(1);
});

test('compute avatar URL', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    const { avatarUrl } = wrapper.vm;
    expect(avatarUrl({ preference: 'default', tid_steve: 1 })).toBe('/avatar/35/1');
    expect(avatarUrl({ preference: 'slim', tid_alex: 1 })).toBe('/avatar/35/1');
});

test('select texture', async () => {
    axios.mockResolvedValue({ data: {} });
    axios.post.mockResolvedValueOnce({ data: { type: 'steve', hash: 'a' } })
        .mockResolvedValueOnce({ data: { type: 'cape', hash: 'b' } });

    const wrapper = mount(Closet);
    wrapper.setData({ skinItems: [{ tid: 1 }] });
    wrapper.find(ClosetItem).vm.$emit('select');
    await wrapper.vm.$nextTick();
    expect(axios.post).toBeCalledWith('/skinlib/info/1');
    expect(wrapper.vm.skinUrl).toBe('/textures/a');

    wrapper.setData({ skinItems: [], capeItems: [{ tid: 2 }], category: 'cape' });
    wrapper.find(ClosetItem).vm.$emit('select');
    await wrapper.vm.$nextTick();
    expect(axios.post).toBeCalledWith('/skinlib/info/2');
    expect(wrapper.vm.capeUrl).toBe('/textures/b');
});

test('apply texture', async () => {
    jest.useFakeTimers();
    window.$ = jest.fn(() => ({
        iCheck: () => ({
            on(evt, cb) {
                cb();
            },
        }),
        0: {
            dispatchEvent: () => {}
        }
    }));
    axios.mockResolvedValue({ data: {} });
    axios.get.mockResolvedValueOnce({ data: [] })
        .mockResolvedValueOnce({ data: [
            { pid: 1, player_name: 'name', preference: 'default', tid_steve: 10 }
        ] });

    const wrapper = mount(Closet);
    const button = wrapper.find(Previewer).findAll('button').at(0);
    button.trigger('click');
    jest.runAllTicks();
    expect(wrapper.find('.modal-body').text()).toContain('user.closet.use-as.empty');

    button.trigger('click');
    await wrapper.vm.$nextTick();
    expect(wrapper.find('input[type="radio"]').attributes()).toHaveProperty('value', '1');
    expect(wrapper.find('.model-label > img').attributes())
        .toHaveProperty('src', '/avatar/35/10');
    expect(wrapper.find('.modal-body').text()).toContain('name');
    jest.runAllTimers();

    jest.useRealTimers();
});

test('submit applying texture', async () => {
    window.$ = jest.fn(() => ({ modal() {} }));
    jest.spyOn(toastr, 'info');
    axios.mockResolvedValue({ data: {} });
    axios.post.mockResolvedValueOnce({ data: { errno: 1 } })
        .mockResolvedValue({ data: { errno: 0, msg: 'ok' } });
    const wrapper = mount(Closet);
    const button = wrapper.find('.modal-footer > a:nth-child(2)');

    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptySelectedPlayer');

    wrapper.setData({ selectedPlayer: 1 });
    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptySelectedTexture');

    wrapper.setData({ selectedSkin: 1 });
    button.trigger('click');
    expect(axios.post).toBeCalledWith(
        '/user/player/set',
        {
            pid: 1,
            tid: {
                skin: 1,
                cape: undefined
            }
        }
    );

    wrapper.setData({ selectedSkin: 0, selectedCape: 1 });
    button.trigger('click');
    expect(axios.post).toBeCalledWith(
        '/user/player/set',
        {
            pid: 1,
            tid: {
                skin: undefined,
                cape: 1
            }
        }
    );
    await wrapper.vm.$nextTick();
    expect(swal).toBeCalledWith({ type: 'success', html: 'ok' });
});

test('reset selected texture', () => {
    axios.mockResolvedValue({ data: {} });
    const wrapper = mount(Closet);
    wrapper.setData({
        selectedSkin: 1,
        selectedCape: 2,
        skinUrl: 'a',
        capeUrl: 'b'
    });
    wrapper.find(Previewer).findAll('button').at(1).trigger('click');
    expect(wrapper.vm).toEqual(expect.objectContaining({
        selectedSkin: 0,
        selectedCape: 0,
        skinUrl: '',
        capeUrl: ''
    }));
});
