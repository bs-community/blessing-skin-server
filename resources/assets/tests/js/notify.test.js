import $ from 'jquery';
import Swal from 'sweetalert2';
import * as notify from '@/js/notify';

jest.mock('sweetalert2', () => ({
    mixin() {
        return this;
    },
    fire() {}
}));

test('show message', () => {
    document.body.innerHTML = '<div id=msg class="callout-x"></div>';
    notify.showMsg('hi');

    const element = $('#msg');
    expect(element.hasClass('callout')).toBeTrue();
    expect(element.hasClass('callout-info')).toBeTrue();
    expect(element.html()).toBe('hi');
});

test('show AJAX error', () => {
    $.fn.modal = function () {
        document.body.innerHTML = this.html();
    };

    notify.showAjaxError(new Error('an-error'));
    expect(document.body.innerHTML).toContain('an-error');
});

test('show modal', () => {
    notify.showModal('message');
    expect($('.modal-title').html()).toBe('Message');

    notify.showModal('message', '', 'default', {
        callback: () => undefined,
        destroyOnClose: false
    });
});

test('show sweetalert', () => {
    jest.spyOn(Swal, 'fire');
    notify.swal({});
    expect(Swal.fire).toBeCalled();
});
