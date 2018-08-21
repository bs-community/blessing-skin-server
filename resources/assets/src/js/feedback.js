import { queryStringify } from './utils';

async function sendFeedback() {
    if (document.cookie.replace(/(?:(?:^|.*;\s*)feedback_sent\s*=\s*([^;]*).*$)|^.*$/, '$1') !== '') {
        return;
    }

    const response = await fetch('https://work.prinzeugen.net/statistics/feedback', {
        body: queryStringify({
            site_name: blessing.site_name,
            site_url: blessing.base_url,
            version: blessing.version
        }),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        method: 'POST',
        mode: 'cors'
    });

    if (response.ok) {
        const { errno } = await response.json();

        if (errno === 0) {
            // It will be expired when current session ends
            document.cookie = 'feedback_sent=' + Date.now();

            console.info('Feedback sent. Thank you!');
        }
    }
}

window.sendFeedback = sendFeedback;
