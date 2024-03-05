import {
	expect, test, vi, it, beforeEach,
} from 'vitest';
import {render, fireEvent, waitFor} from '@testing-library/react';
import {createPaginator} from '../../utils';
import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import {TextureType} from '@/scripts/types';
import urls from '@/scripts/urls';
import SkinLibrary from '@/views/skinlib/SkinLibrary';
import type {LibraryItem} from '@/views/skinlib/SkinLibrary/types';

vi.mock('@/scripts/net');

const fixtureItem: Readonly<LibraryItem> = Object.freeze<LibraryItem>({
	tid: 1,
	name: 'my skin',
	type: TextureType.Steve,
	uploader: 1,
	nickname: 'me',
	public: true,
	likes: 70,
});

beforeEach(() => {
	window.blessing.extra = {currentUid: null};
});

test('without authenticated', async () => {
	fetch.get.mockResolvedValue(createPaginator([]));

	const {queryByText} = render(<SkinLibrary/>);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	expect(fetch.get).toBeCalledWith(
		urls.skinlib.list(),
		expect.toSatisfy((search: URLSearchParams) => {
			expect(search.get('filter')).toBe('skin');
			expect(search.get('sort')).toBe('time');
			expect(search.get('page')).toBe('1');
			return true;
		}),
	);
	expect(fetch.get).not.toBeCalledWith('/user/closet/ids');
	expect(queryByText(t('skinlib.seeMyUpload'))).not.toBeInTheDocument();
});

test('search by keyword', async () => {
	fetch.get.mockResolvedValue({...createPaginator([]), last_page: 10});

	const {getByText, getByTitle, getByPlaceholderText} = render(
		<SkinLibrary/>,
	);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	// Test for resetting page
	fireEvent.click(getByText('2'));

	fireEvent.input(getByPlaceholderText(t('vendor.datatable.search')), {
		target: {value: 'k'},
	});
	fireEvent.click(getByTitle(t('vendor.datatable.search')));
	await waitFor(() => {
		expect(fetch.get).toHaveBeenLastCalledWith(
			urls.skinlib.list(),
			expect.toSatisfy((search: URLSearchParams) => {
				expect(search.get('keyword')).toBe('k');
				expect(search.get('page')).toBe('1');
				return true;
			}),
		);
	},
	);
});

test('select uploaded by self', async () => {
	window.blessing.extra.currentUid = 1;
	fetch.get.mockResolvedValue({...createPaginator([]), last_page: 10});

	const {getByText, queryByText} = render(<SkinLibrary/>);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	// Test for resetting page
	fireEvent.click(getByText('2'));

	fireEvent.click(getByText(t('skinlib.seeMyUpload')));
	await waitFor(() => {
		expect(fetch.get).toHaveBeenLastCalledWith(
			urls.skinlib.list(),
			expect.toSatisfy((search: URLSearchParams) => {
				expect(search.get('uploader')).toBe('1');
				expect(search.get('page')).toBe('1');
				return true;
			}),
		);
	},
	);
	expect(queryByText(t('skinlib.filter.uploader', {uid: 1})));
});

test('reset query', async () => {
	window.blessing.extra.currentUid = 1;
	fetch.get.mockResolvedValue(createPaginator([]));

	const {getByText, getByTitle, getByPlaceholderText, queryByText} = render(
		<SkinLibrary/>,
	);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	fireEvent.click(getByText('Steve'));
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});
	fireEvent.input(getByPlaceholderText(t('vendor.datatable.search')), {
		target: {value: 'k'},
	});
	fireEvent.click(getByTitle(t('vendor.datatable.search')));
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});
	fireEvent.click(getByText(t('skinlib.seeMyUpload')));
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});
	fireEvent.click(getByText(t('skinlib.sort.likes')));
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});
	fireEvent.click(getByText(t('skinlib.reset')));
	await waitFor(() => {
		expect(fetch.get).toHaveBeenLastCalledWith(
			urls.skinlib.list(),
			expect.toSatisfy((search: URLSearchParams) => {
				expect(search.get('filter')).toBe('skin');
				expect(search.get('keyword')).toBeNull();
				expect(search.get('uploader')).toBeNull();
				expect(search.get('sort')).toBe('time');
				expect(search.get('page')).toBe('1');
				return true;
			}),
		);
	},
	);
	expect(queryByText(t('skinlib.filter.uploader', {uid: 1})));
});

test('browser goes back', async () => {
	fetch.get.mockResolvedValue(createPaginator([]));

	const {getByText} = render(<SkinLibrary/>);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	fireEvent.click(getByText('Steve'));
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	const {state} = window.history;
	const event = new PopStateEvent('popstate', {
		state: state.replace('steve', 'skin'),
	});
	window.dispatchEvent(event);
	await waitFor(() => {
		expect(fetch.get).toHaveBeenLastCalledWith(
			urls.skinlib.list(),
			expect.toSatisfy((search: URLSearchParams) => {
				expect(search.get('filter')).toBe('skin');
				return true;
			}),
		);
	},
	);
});

test('pagination', async () => {
	const response = {...createPaginator([]), last_page: 2};
	fetch.get.mockResolvedValue(response);

	const {getByText} = render(<SkinLibrary/>);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	fireEvent.click(getByText('2'));

	expect(fetch.get).toHaveBeenLastCalledWith(
		urls.skinlib.list(),
		expect.toSatisfy((search: URLSearchParams) => {
			expect(search.get('page')).toBe('2');
			return true;
		}),
	);
});

test('library item', async () => {
	fetch.get.mockResolvedValue(createPaginator([fixtureItem]));

	const {getByText, queryByText, queryAllByText, queryByAltText} = render(
		<SkinLibrary/>,
	);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	expect(queryAllByText('Steve')).toHaveLength(2);
	expect(queryByText(fixtureItem.name)).toBeInTheDocument();
	expect(queryByAltText(fixtureItem.name)).toHaveAttribute(
		'src',
		`/preview/${fixtureItem.tid}?height=150&png`,
	);
	expect(queryByText(fixtureItem.nickname)).toBeInTheDocument();

	fireEvent.click(getByText(fixtureItem.nickname));
	await waitFor(() => {
		expect(fetch.get).toHaveBeenLastCalledWith(
			urls.skinlib.list(),
			expect.toSatisfy((search: URLSearchParams) => {
				expect(search.get('uploader')).toBe(fixtureItem.uploader.toString());
				return true;
			}),
		);
	},
	);
	const search = new URLSearchParams(location.search);
	expect(search.get('uploader')).toBe(fixtureItem.uploader.toString());
});

test('private texture', async () => {
	const item = {...fixtureItem, public: false};
	fetch.get.mockResolvedValue(createPaginator([item]));

	const {queryByTitle} = render(<SkinLibrary/>);
	await waitFor(() => {
		expect(fetch.get).toBeCalled();
	});

	expect(queryByTitle(t('skinlib.private'))).toBeInTheDocument();
});

describe('by filter', () => {
	beforeEach(() => {
		fetch.get.mockResolvedValue({...createPaginator([]), last_page: 10});
	});

	it('skin', async () => {
		const {getByText, queryAllByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		// Test for resetting page
		fireEvent.click(getByText('2'));

		fireEvent.click(getByText('Steve'));
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});
		fireEvent.click(getByText(t('general.skin')));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('filter')).toBe('skin');
					expect(search.get('page')).toBe('1');
					return true;
				}),
			);
		},
		);
		expect(queryAllByText(t('general.skin'))).toHaveLength(2);
		const search = new URLSearchParams(location.search);
		expect(search.get('filter')).toBe('skin');
	});

	it('steve', async () => {
		const {getByText, queryAllByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		// Test for resetting page
		fireEvent.click(getByText('2'));

		fireEvent.click(getByText('Steve'));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('filter')).toBe('steve');
					expect(search.get('page')).toBe('1');
					return true;
				}),
			);
		},
		);
		expect(queryAllByText('Steve')).toHaveLength(2);
		const search = new URLSearchParams(location.search);
		expect(search.get('filter')).toBe('steve');
	});

	it('alex', async () => {
		const {getByText, queryAllByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		// Test for resetting page
		fireEvent.click(getByText('2'));

		fireEvent.click(getByText('Alex'));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('filter')).toBe('alex');
					expect(search.get('page')).toBe('1');
					return true;
				}),
			);
		},
		);
		expect(queryAllByText('Alex')).toHaveLength(2);
		const search = new URLSearchParams(location.search);
		expect(search.get('filter')).toBe('alex');
	});

	it('cape', async () => {
		const {getByText, queryAllByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		// Test for resetting page
		fireEvent.click(getByText('2'));

		fireEvent.click(getByText(t('general.cape')));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('filter')).toBe('cape');
					expect(search.get('page')).toBe('1');
					return true;
				}),
			);
		},
		);
		expect(queryAllByText(t('general.cape'))).toHaveLength(2);
		const search = new URLSearchParams(location.search);
		expect(search.get('filter')).toBe('cape');
	});
});

describe('sorting', () => {
	beforeEach(() => {
		fetch.get.mockResolvedValue(createPaginator([]));
	});

	it('by time', async () => {
		const {getByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(t('skinlib.sort.likes')));
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});
		fireEvent.click(getByText(t('skinlib.sort.time')));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('sort')).toBe('time');
					return true;
				}),
			);
		},
		);
		const search = new URLSearchParams(location.search);
		expect(search.get('sort')).toBe('time');
	});

	it('by likes', async () => {
		const {getByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(t('skinlib.sort.likes')));
		await waitFor(() => {
			expect(fetch.get).toHaveBeenLastCalledWith(
				urls.skinlib.list(),
				expect.toSatisfy((search: URLSearchParams) => {
					expect(search.get('sort')).toBe('likes');
					return true;
				}),
			);
		},
		);
		const search = new URLSearchParams(location.search);
		expect(search.get('sort')).toBe('likes');
	});
});

describe('add to closet', () => {
	beforeEach(() => {
		fetch.get.mockImplementation(async (url: string) => {
			if (url === urls.skinlib.list()) {
				return createPaginator([fixtureItem]);
			}

			return [];
		});
	});

	it('without authenticated', async () => {
		const {getByText, queryByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(fixtureItem.likes.toString()));
		expect(queryByText(t('skinlib.anonymous'))).toBeInTheDocument();
		expect(fetch.post).not.toBeCalled();
	});

	it('succeeded', async () => {
		window.blessing.extra.currentUid = 1;
		fetch.post.mockResolvedValue({code: 0, message: 'ok'});

		const {getByText, queryByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(fixtureItem.likes.toString()));
		fireEvent.click(getByText(t('general.confirm')));
		await waitFor(() => {
			expect(fetch.post).toBeCalled();
		});
		expect(queryByText((fixtureItem.likes + 1).toString())).toBeInTheDocument();
	});

	it('failed', async () => {
		window.blessing.extra.currentUid = 1;
		fetch.post.mockResolvedValue({code: 1, message: 'failed'});

		const {getByText, queryByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(fixtureItem.likes.toString()));
		fireEvent.click(getByText(t('general.confirm')));
		await waitFor(() => {
			expect(fetch.post).toBeCalled();
		});
		expect(queryByText(fixtureItem.likes.toString())).toBeInTheDocument();
	});
});

describe('remove from closet', () => {
	beforeEach(() => {
		window.blessing.extra.currentUid = 1;
		fetch.get.mockImplementation(async (url: string) => {
			if (url === urls.skinlib.list()) {
				return createPaginator([fixtureItem]);
			}

			return [fixtureItem.tid];
		});
	});

	it('succeeded', async () => {
		fetch.del.mockResolvedValue({code: 0, message: 'ok'});

		const {getByText, queryByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(fixtureItem.likes.toString()));
		fireEvent.click(getByText(t('general.confirm')));
		await waitFor(() => {
			expect(fetch.del).toBeCalled();
		});
		expect(queryByText((fixtureItem.likes - 1).toString())).toBeInTheDocument();
	});

	it('failed', async () => {
		fetch.del.mockResolvedValue({code: 1, message: 'failed'});

		const {getByText, queryByText} = render(<SkinLibrary/>);
		await waitFor(() => {
			expect(fetch.get).toBeCalled();
		});

		fireEvent.click(getByText(fixtureItem.likes.toString()));
		fireEvent.click(getByText(t('general.confirm')));
		await waitFor(() => {
			expect(fetch.del).toBeCalled();
		});
		expect(queryByText(fixtureItem.likes.toString())).toBeInTheDocument();
	});
});
