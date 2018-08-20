import Vue from 'vue';
import { mount } from '@vue/test-utils';
import List from '@/components/skinlib/List';

test('fetch data before mounting', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    mount(List);
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 1 }
    );
});

test('empty skin library', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    expect(wrapper.text()).toContain('general.noResult');
});

test('toggle texture type', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    window.$ = jest.fn(() => ({
        iCheck() {}
    }));
    const wrapper = mount(List);
    const breadcrumb = wrapper.find('.breadcrumb');

    jest.runAllTimers();
    expect(breadcrumb.text()).toContain('skinlib.filter.skin');

    wrapper.find('[value=steve]').setChecked();
    jest.runAllTimers();
    expect(breadcrumb.text()).toContain('skinlib.filter.steve');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'steve', uploader: 0, sort: 'time', keyword: '', page: 1 }
    );

    wrapper.find('[value=alex]').setChecked();
    jest.runAllTimers();
    expect(breadcrumb.text()).toContain('skinlib.filter.alex');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'alex', uploader: 0, sort: 'time', keyword: '', page: 1 }
    );

    wrapper.find('[value=cape]').setChecked();
    jest.runAllTimers();
    expect(breadcrumb.text()).toContain('general.cape');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'cape', uploader: 0, sort: 'time', keyword: '', page: 1 }
    );
});

test('check specified uploader', async () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 1
    });
    const wrapper = mount(List);
    await wrapper.vm.$nextTick();
    const breadcrumb = wrapper.find('.breadcrumb');
    const button = wrapper.find('.btn-default');

    jest.runAllTimers();
    expect(breadcrumb.text()).toContain('skinlib.filter.allUsers');

    button.trigger('click');
    expect(breadcrumb.text()).toContain('skinlib.filter.uploader');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 1, sort: 'time', keyword: '', page: 1 }
    );
});

test('sort items', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    const sortByLikes = wrapper.find('.dropdown-menu > li:nth-child(1) > a');
    const sortByTime = wrapper.find('.dropdown-menu > li:nth-child(2) > a');
    jest.runAllTimers();

    sortByLikes.trigger('click');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'likes', keyword: '', page: 1 }
    );
    expect(wrapper.text()).toContain('skinlib.sort.likes');
    jest.runAllTimers();

    sortByTime.trigger('click');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 1 }
    );
    expect(wrapper.text()).toContain('skinlib.sort.time');
});

test('search by keyword', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    const searchBox = wrapper.find('input[type=text]');
    jest.runAllTimers();

    searchBox.setValue('a');
    wrapper.find('form').trigger('submit');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'time', keyword: 'a', page: 1 }
    );
    jest.runAllTimers();

    searchBox.setValue('b');
    wrapper.find('.input-group-btn > button').trigger('click');
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'time', keyword: 'b', page: 1 }
    );
});

test('reset all filters', async () => {
    window.$ = jest.fn(() => ({
        iCheck() {}
    }));
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    jest.runAllTimers();
    wrapper.find('input[value=cape]').setChecked();
    jest.runAllTimers();
    wrapper.find('input[type=text]').setValue('abc');
    jest.runAllTimers();
    wrapper.find('.dropdown-menu > li:nth-child(1) > a').trigger('click');
    jest.runAllTimers();

    Vue.prototype.$http.get.mockClear();
    wrapper.find('.btn-warning').trigger('click');
    expect(Vue.prototype.$http.get).toHaveBeenCalledTimes(1);
});

test('is anonymous', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    expect(wrapper.vm.anonymous).toBeTrue();
});

test('on page changed', () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [], total_pages: 0, current_uid: 0
    });
    const wrapper = mount(List);
    jest.runAllTimers();
    wrapper.vm.pageChanged(2);
    expect(Vue.prototype.$http.get).toBeCalledWith(
        '/skinlib/data',
        { filter: 'skin', uploader: 0, sort: 'time', keyword: '', page: 2 }
    );
});

test('on like toggled', async () => {
    Vue.prototype.$http.get.mockResolvedValue({
        items: [{ tid: 1, liked: false, likes: 0 }], total_pages: 1, current_uid: 0
    });
    const wrapper = mount(List);
    await wrapper.vm.$nextTick();
    wrapper.vm.onLikeToggled(0, true);
    expect(wrapper.vm.items[0].liked).toBeTrue();
    expect(wrapper.vm.items[0].likes).toBe(1);

    wrapper.vm.onLikeToggled(0, false);
    expect(wrapper.vm.items[0].liked).toBeFalse();
    expect(wrapper.vm.items[0].likes).toBe(0);
});
