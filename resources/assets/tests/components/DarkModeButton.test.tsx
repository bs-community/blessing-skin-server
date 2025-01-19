import DarkModeButton from '@/components/DarkModeButton';
import * as fetch from '@/scripts/net';
import {fireEvent, render, waitFor} from '@testing-library/react';
import {expect, vi} from 'vitest';

vi.mock('@/scripts/net');

it('click to toggle', async () => {
	const {getByRole} = render(<DarkModeButton initMode={false}/>);
	const button = getByRole('button');

	fireEvent.click(button);
	await waitFor(() => {
		expect(fetch.put).toBeCalledWith('/user/dark-mode');
	});
});

it('default is dark', async () => {
	const {getByRole} = render(<DarkModeButton initMode/>);
	const button = getByRole('button');

	fireEvent.click(button);
	await waitFor(() => {
		expect(fetch.put).toBeCalledWith('/user/dark-mode');
	});
});
