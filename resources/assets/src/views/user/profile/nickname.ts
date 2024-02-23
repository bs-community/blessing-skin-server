import {post, type ResponseBody} from '@/scripts/net';
import {showModal} from '@/scripts/notify';

export default async function handler(event: Event) {
	event.preventDefault();

	const form = event.target as HTMLFormElement;
	const nickname: string = form.nickname.value;

	const {code, message}: ResponseBody = await post(
		'/user/profile?action=nickname',
		{
			new_nickname: nickname,
		},
	);
	showModal({mode: 'alert', text: message});
	if (code === 0) {
		for (const element of document.querySelectorAll('[data-mark="nickname"]')) {
			element.textContent = nickname;
		}
	}
}
