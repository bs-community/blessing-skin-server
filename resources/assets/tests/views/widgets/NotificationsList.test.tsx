import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import NotificationsList, {
	type Notification,
} from '@/views/widgets/NotificationsList';
import {fireEvent, render, waitFor} from '@testing-library/react';
import {expect, vi} from 'vitest';

vi.mock('@/scripts/net');

beforeEach(() => {
	document.body.innerHTML = '';
});

function createContainer(notifications: Notification[]) {
	const container = document.createElement('div');
	container.dataset.notifications = JSON.stringify(notifications);
	container.dataset.t = 'no unread';
	document.body.append(container);
}

it('should not throw if element does not exist', () => {
	render(<NotificationsList/>);
});

it('no unread notifications', () => {
	createContainer([]);

	const {queryByText} = render(<NotificationsList/>);

	expect(queryByText('no unread')).toBeInTheDocument();
});

it('with unread notifications', () => {
	createContainer([{id: '1', title: 'hi'}]);

	const {queryByText} = render(<NotificationsList/>);

	expect(queryByText('1')).toBeInTheDocument();
	expect(queryByText('hi')).toBeInTheDocument();
});

it('read notification', async () => {
	const time = new Date().toLocaleTimeString();
	const fixture = {
		title: 'hi - title',
		content: 'content here',
		time,
	};

	createContainer([{id: '1', title: 'hi'}]);
	fetch.post.mockResolvedValue(fixture);

	const {getByText, queryByText} = render(<NotificationsList/>);

	fireEvent.click(getByText('hi'));
	await waitFor(() => {
		expect(fetch.post).toBeCalled();
	});

	expect(queryByText(fixture.title)).toBeInTheDocument();
	expect(queryByText(fixture.content)).toBeInTheDocument();
	expect(queryByText(fixture.time)).toBeInTheDocument();
	expect(queryByText('no unread')).toBeInTheDocument();
	expect(queryByText('1')).not.toBeInTheDocument();

	fireEvent.click(getByText(t('general.confirm')));
});
