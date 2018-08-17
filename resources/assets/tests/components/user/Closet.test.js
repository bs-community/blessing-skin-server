import Vue from 'vue';
import { mount } from '@vue/test-utils';
import Closet from '@/components/user/Closet';
import ClosetItem from '@/components/user/ClosetItem';
import Previewer from '@/components/common/Previewer';
import toastr from 'toastr';
import { swal } from '@/js/notify';

jest.mock('@/js/notify');

window.__bs_data__ = { unverified: false };

test('fetch closet data before mount', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    mount(Closet);
    jest.runAllTicks();
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/user/closet-data',
        {
            category: 'skin',
            q: '',
            page: 1,
        }
    );
});

test('switch tabs', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [],
        category: 'skin',
        total_pages: 1
    }).mockResolvedValueOnce({
        items: [],
        category: 'cape',
        total_pages: 1
    });

    const wrapper = mount(Closet);

    const tabSkin = wrapper.findAll('.nav-tabs > li').at(0);
    tabSkin.find('a').trigger('click');
    jest.runAllTicks();
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/user/closet-data',
        {
            category: 'skin',
            q: '',
            page: 1,
        }
    );

    const tabCape = wrapper.findAll('.nav-tabs > li').at(1);
    tabCape.find('a').trigger('click');
    jest.runAllTicks();
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/user/closet-data',
        {
            category: 'cape',
            q: '',
            page: 1,
        }
    );
});

test('different categories', () => {
    Vue.prototype.$http.get.mockResolvedValue({});

    const wrapper = mount(Closet);
    expect(wrapper.findAll('.nav-tabs > li').at(0).classes()).toContain('active');
    expect(wrapper.find('#skin-category').classes()).toContain('active');

    wrapper.setData({ category: 'cape' });
    expect(wrapper.findAll('.nav-tabs > li').at(1).classes()).toContain('active');
    expect(wrapper.find('#cape-category').classes()).toContain('active');
});

test('search textures', () => {
    Vue.prototype.$http.get.mockResolvedValue({});

    const wrapper = mount(Closet);
    const input = wrapper.find('input');
    input.element.value = 'q';
    input.trigger('input');
    jest.runAllTimers();
    jest.runAllTicks();
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/user/closet-data',
        {
            category: 'skin',
            q: 'q',
            page: 1,
        }
    );
});

test('empty closet', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    expect(wrapper.find('#skin-category').text()).toContain('user.emptyClosetMsg');
    wrapper.setData({ category: 'cape' });
    expect(wrapper.find('#cape-category').text()).toContain('user.emptyClosetMsg');
});

test('no matched search result', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    wrapper.setData({ query: 'q' });
    expect(wrapper.find('#skin-category').text()).toContain('general.noResult');
    wrapper.setData({ category: 'cape' });
    expect(wrapper.find('#cape-category').text()).toContain('general.noResult');
});

test('render items', async () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [
            { tid: 1 },
            { tid: 2 }
        ],
        category: 'skin',
        total_pages: 1
    });
    const wrapper = mount(Closet);
    await wrapper.vm.$nextTick();
    expect(wrapper.findAll(ClosetItem)).toHaveLength(2);
});

test('reload closet when page changed', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    wrapper.vm.pageChanged();
    jest.runAllTicks();
    expect(Vue.prototype.$http.get).toHaveBeenCalledTimes(2);
});

test('remove skin item', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    wrapper.setData({ skinItems: [{ tid: 1 }, { tid: 2 }] });
    wrapper.vm.removeSkinItem(1);
    expect(wrapper.findAll(ClosetItem)).toHaveLength(1);
});

test('remove cape item', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    wrapper.setData({ capeItems: [{ tid: 1 }, { tid: 2 }], category: 'cape' });
    wrapper.vm.removeCapeItem(1);
    expect(wrapper.findAll(ClosetItem)).toHaveLength(1);
});

test('compute avatar URL', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
    const wrapper = mount(Closet);
    const { avatarUrl } = wrapper.vm;
    expect(avatarUrl({ preference: 'default', tid_steve: 1 })).toBe('/avatar/35/1');
    expect(avatarUrl({ preference: 'slim', tid_alex: 1 })).toBe('/avatar/35/1');
});

test('select texture', async () => {
    Vue.prototype.$http.get
        .mockResolvedValueOnce({})
        .mockResolvedValueOnce({ type: 'steve', hash: 'a' })
        .mockResolvedValueOnce({ type: 'cape', hash: 'b' });

    const wrapper = mount(Closet);
    wrapper.setData({ skinItems: [{ tid: 1 }] });
    wrapper.find(ClosetItem).vm.$emit('select');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/1');
    expect(wrapper.vm.skinUrl).toBe('/textures/a');

    wrapper.setData({ skinItems: [], capeItems: [{ tid: 2 }], category: 'cape' });
    wrapper.find(ClosetItem).vm.$emit('select');
    await wrapper.vm.$nextTick();
    expect(Vue.prototype.$http.get).toBeCalledWith('/skinlib/info/2');
    expect(wrapper.vm.capeUrl).toBe('/textures/b');
});

test('apply texture', async () => {
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
    Vue.prototype.$http.get
        .mockResolvedValueOnce({})
        .mockResolvedValueOnce([])
        .mockResolvedValueOnce([
            { pid: 1, player_name: 'name', preference: 'default', tid_steve: 10 }
        ]);

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
});

test('submit applying texture', async () => {
    window.$ = jest.fn(() => ({ modal() {} }));
    jest.spyOn(toastr, 'info');
    Vue.prototype.$http.get.mockResolvedValue({});
    Vue.prototype.$http.post.mockResolvedValueOnce({ errno: 1 })
        .mockResolvedValue({ errno: 0, msg: 'ok' });
    const wrapper = mount(Closet);
    const button = wrapper.find('.modal-footer > a:nth-child(2)');

    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptySelectedPlayer');

    wrapper.setData({ selectedPlayer: 1 });
    button.trigger('click');
    expect(toastr.info).toBeCalledWith('user.emptySelectedTexture');

    wrapper.setData({ selectedSkin: 1 });
    button.trigger('click');
    expect(Vue.prototype.$http.post).toBeCalledWith(
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
    expect(Vue.prototype.$http.post).toBeCalledWith(
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
    expect(swal).toBeCalledWith({ type: 'success', text: 'ok' });
});

test('reset selected texture', () => {
    Vue.prototype.$http.get.mockResolvedValue({});
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

test('select specified texture initially', async () => {
    window.history.pushState({}, 'title', 'about:blank?tid=1');
    window.$ = jest.fn(() => ({
        modal() {},
        iCheck: () => ({
            on(evt, cb) {
                cb();
            },
        }),
        0: {
            dispatchEvent: () => {}
        }
    }));
    Vue.prototype.$http.get
        .mockResolvedValueOnce({
            items: [],
            category: 'skin',
            total_pages: 1
        })
        .mockResolvedValueOnce({ type: 'cape', hash: '' })
        .mockResolvedValueOnce([]);
    const wrapper = mount(Closet);
    jest.runAllTimers();
    await wrapper.vm.$nextTick();
    jest.unmock('@/js/utils');
    window.history.pushState({}, 'title', 'about:blank');
});
