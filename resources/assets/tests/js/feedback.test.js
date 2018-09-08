import { sendFeedback } from '@/js/feedback';

test('send feedback', async () => {
    window.fetch = jest.fn()
        .mockResolvedValueOnce({ ok: false })
        .mockResolvedValueOnce({
            ok: true,
            json: () => Promise.resolve({ errno: 1 })
        })
        .mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({ errno: 0 })
        });

    await sendFeedback();
    expect(document.cookie).toBe('');
    expect(fetch.mock.calls[0]).toMatchSnapshot();

    await sendFeedback();
    expect(document.cookie).toBe('');

    await sendFeedback();
    expect(document.cookie).toStartWith('feedback_sent=');

    window.fetch.mockClear();
    await sendFeedback();
    expect(window.fetch).not.toBeCalled();
});
