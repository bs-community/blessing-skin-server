import {Toast} from '@/scripts/toast';
import {fireEvent, render, screen} from '@testing-library/react';

it('"Toast" class', () => {
	const toast = new Toast(render);

	toast.success('success');
	expect(document.querySelector('.alert-success')!.textContent).toContain('success');

	toast.info('info');
	expect(document.querySelector('.alert-info')!.textContent).toContain('info');

	toast.warning('warning');
	expect(document.querySelector('.alert-warning')!.textContent).toContain('warning');

	toast.error('error');
	expect(document.querySelector('.alert-danger')!.textContent).toContain('error');

	vi.runAllTimers();
	toast.dispose();
});

it('clear toasts', () => {
	const toast = new Toast(render);

	toast.success('success');
	toast.info('info');

	toast.clear();
	expect(document.querySelectorAll('.alert')).toHaveLength(0);

	toast.dispose();
});

it('close toast manually', () => {
	const toast = new Toast(render);
	toast.success('success');

	fireEvent.click(screen.getByText('×'));
	expect(document.querySelectorAll('.alert')).toHaveLength(0);

	toast.dispose();
});
