import React, {useState, useEffect} from 'react';
import * as fetch from '@/scripts/net';
import {showModal} from '@/scripts/notify';

export type Notification = {
	id: string;
	title: string;
};

const NotificationsList: React.FC = () => {
	const [notifications, setNotifications] = useState<Notification[]>([]);
	const [noUnreadText, setNoUnreadText] = useState('');

	useEffect(() => {
		const dataset = document.querySelector<HTMLLIElement>(
			'[data-notifications]',
		)?.dataset;
		if (dataset) {
			const notifications: Notification[] = JSON.parse(dataset.notifications!);
			setNotifications(notifications);
			setNoUnreadText(dataset.t!);
		}
	}, []);

	const read = async (id: string) => {
		const {title, content, time} = await fetch.post<{
			title: string;
			content: string;
			time: string;
		}>(`/user/notifications/${id}`);

		showModal({
			mode: 'alert',
			title,
			children: (
				<>
					<div dangerouslySetInnerHTML={{__html: content}}/>
					<br/>
					<small>{time}</small>
				</>
			),
		});
		setNotifications(notifications =>
			notifications.filter(notification => notification.id !== id),
		);
	};

	const hasUnread = notifications.length > 0;

	return (
		<>
			<a className='nav-link' data-toggle='dropdown' href='#'>
				<i className='far fa-bell'/>
				{hasUnread && (
					<span className='badge badge-warning navbar-badge'>
						{notifications.length}
					</span>
				)}
			</a>
			<div className='dropdown-menu dropdown-menu-lg dropdown-menu-right'>
				{hasUnread ? (
					notifications.map(notification => (
						<>
							<a
								key={notification.id}
								href='#'
								className='dropdown-item'
								onClick={async () => read(notification.id)}
							>
								<i className='far fa-circle text-info mr-2'/>
								{notification.title}
							</a>
							<div className='dropdown-divider'/>
						</>
					))
				) : (
					<p className='text-center text-muted pt-2 pb-2'>{noUnreadText}</p>
				)}
			</div>
		</>
	);
};

export default NotificationsList;
