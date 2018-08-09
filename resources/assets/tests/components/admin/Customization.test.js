import Vue from 'vue';
import { mount } from '@vue/test-utils';
import Customization from '@/components/admin/Customization';

window.currentSkin = 'skin-blue';

test('preview color', () => {
    document.body.classList.add('skin-blue');
    const wrapper = mount(Customization);
    wrapper.findAll('a').at(2).trigger('click');
    expect(document.body.classList.contains('skin-blue')).toBeFalse();
    expect(document.body.classList.contains('skin-yellow')).toBeTrue();
});

test('submit color', () => {
    Vue.prototype.$http.post.mockResolvedValue({ errno: 0, msg: '' });
    const wrapper = mount(Customization);
    wrapper.findAll('a').at(4).trigger('click');
    wrapper.find('button').trigger('click');
    expect(Vue.prototype.$http.post).toBeCalledWith(
        '/admin/customize?action=color',
        { color_scheme: 'skin-green' }
    );
});
