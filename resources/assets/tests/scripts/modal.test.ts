import {t} from '@/scripts/i18n';
import {showModal} from '@/scripts/modal';

test('show modal', async () => {
	Promise.resolve().then(() => {
		expect(document.querySelector('.modal-title')!.textContent).toBe(
			t('general.tip'),
		);
		document.querySelector<HTMLButtonElement>('.btn-primary')!.click();
	});
	const {value} = await showModal();
	expect(value).toBe('');
});
