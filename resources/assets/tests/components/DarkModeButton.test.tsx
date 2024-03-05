import {expect, vi, test} from 'vitest';
import {render, fireEvent, waitFor} from '@testing-library/react';
import * as fetch from '@/scripts/net';
import DarkModeButton from '@/components/DarkModeButton';

vi.mock('@/scripts/net');

test('click to toggle', async () => {
	const {getByRole} = render(<DarkModeButton initMode={false}/>);
	const button = getByRole('button');

	fireEvent.click(button);
	await waitFor(() => {
		expect(fetch.put).toBeCalledWith('/user/dark-mode');
	});
});

test('default is dark', async () => {
	const {getByRole} = render(<DarkModeButton initMode/>);
	const button = getByRole('button');

	fireEvent.click(button);
	await waitFor(() => {
		expect(fetch.put).toBeCalledWith('/user/dark-mode');
	});
});
