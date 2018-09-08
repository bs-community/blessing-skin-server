import * as net from '@/js/net';
import { on } from '@/js/event';
import { showAjaxError } from '@/js/notify';

jest.mock('@/js/notify');

window.Request = function Request(url, init) {
    this.url = url;
    Object.keys(init).forEach(key => this[key] = init[key]);
    this.headers = new Map(Object.entries(init.headers));
};

test('the GET method', async () => {
    const json = jest.fn().mockResolvedValue({});
    window.fetch = jest.fn().mockResolvedValue({
        ok: true,
        json
    });

    await net.get('/abc', { a: 'b' });
    expect(window.fetch.mock.calls[0][0].url).toBe('/abc?a=b');
    expect(json).toBeCalled();

    await net.get('/abc');
    expect(window.fetch.mock.calls[1][0].url).toBe('/abc');
});

test('the POST method', async () => {
    window.fetch = jest.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({})
    });

    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = 'token';
    document.head.appendChild(meta);

    await net.post('/abc', { a: 'b' });
    const request = window.fetch.mock.calls[0][0];
    expect(request.url).toBe('/abc');
    expect(request.method).toBe('POST');
    expect(request.body).toBe(JSON.stringify({ a: 'b' }));
    expect(request.headers.get('X-CSRF-TOKEN')).toBe('token');

    await net.post('/abc');
    expect(window.fetch.mock.calls[1][0].body).toBe('{}');
});

test('low level fetch', async () => {
    const json = jest.fn().mockResolvedValue({});
    window.fetch = jest.fn()
        .mockRejectedValueOnce(new Error)
        .mockResolvedValueOnce({
            ok: false,
            text: () => Promise.resolve('404')
        })
        .mockResolvedValueOnce({
            ok: true,
            json
        });

    const stub = jest.fn();
    on('beforeFetch', stub);
    const request = { headers: new Map() };

    await net.walkFetch(request);
    expect(showAjaxError.mock.calls[0][0]).toBeInstanceOf(Error);
    expect(stub).toBeCalledWith(request);

    await net.walkFetch(request);
    expect(showAjaxError).toBeCalledWith('404');

    await net.walkFetch(request);
    expect(json).toBeCalled();
});
