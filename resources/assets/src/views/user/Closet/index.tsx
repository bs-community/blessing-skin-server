import {useState, useEffect, useRef} from 'react';
import debounce from 'lodash-es/debounce';
import ClosetItem from './ClosetItem';
import LoadingClosetItem from './LoadingClosetItem';
import Previewer from './Previewer';
import ModalApply from './ModalApply';
import removeClosetItem from './removeClosetItem';
import useEmitMounted from '@/scripts/hooks/useEmitMounted';
import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import {showModal, toast} from '@/scripts/notify';
import {
	type ClosetItem as Item,
	type Texture,
	type Paginator,
	TextureType,
} from '@/scripts/types';
import urls from '@/scripts/urls';
import Pagination from '@/components/Pagination';

type Category = 'skin' | 'cape';

const updater = debounce(
	// eslint-disable-next-line @typescript-eslint/comma-dangle
	<T,>(
		value: React.SetStateAction<T>,
		setter: React.Dispatch<React.SetStateAction<T>>,
	) => {
		setter(value);
	},
	350,
) as <T>(value: React.SetStateAction<T>, setter: React.Dispatch<React.SetStateAction<T>>) => void;

function Closet() {
	const [isLoading, setIsLoading] = useState(true);
	const [category, setCategory] = useState<Category>('skin');
	const [search, setSearch] = useState('');
	const [query, setQuery] = useState('');
	const [page, setPage] = useState(1);
	const [totalPages, setTotalPages] = useState(1);
	const [items, setItems] = useState<Item[]>([]);
	const [skin, setSkin] = useState<Texture | null>(null);
	const [cape, setCape] = useState<Texture | null>(null);
	const [showModalApply, setShowModalApply] = useState(false);
	const containerReference = useRef<HTMLDivElement | null>(null);
	const perPageReference = useRef(6);

	useEmitMounted();

	useEffect(() => {
		const element = containerReference.current;

		if (element) {
			const {width} = element.getBoundingClientRect();
			if (width >= 500) {
				perPageReference.current = Math.floor(width / 235) * 2;
			}
		}
	}, []);

	useEffect(() => {
		const getItems = async () => {
			setIsLoading(true);
			const {data, last_page: lastPage} = await fetch.get<Paginator<Item>>(
				urls.user.closet.list(),
				{
					category, q: query, page: page.toString(), perPage: perPageReference.current.toString(),
				},
			);

			setItems(data);
			setTotalPages(lastPage);
			setIsLoading(false);
		};

		void getItems();
	}, [category, query, page]);

	const switchCategoryToSkin = () => {
		if (category !== 'skin') {
			setCategory('skin');
			setPage(1);
		}
	};

	const switchCategoryToCape = () => {
		if (category !== 'cape') {
			setCategory('cape');
			setPage(1);
		}
	};

	const handleSearch = (event: React.ChangeEvent<HTMLInputElement>) => {
		const {value} = event.target;
		setSearch(value);
		updater(value, setQuery);
	};

	const handlePageChange = (page: number) => {
		setPage(page);
	};

	const isSelected = (item: Item): boolean => {
		if (category === 'skin') {
			return item.tid === skin?.tid;
		}

		return item.tid === cape?.tid;
	};

	const handleSelect = (item: Item) => {
		if (item.type === TextureType.Cape) {
			setCape(item);
		} else {
			setSkin(item);
		}
	};

	const resetSelected = () => {
		setSkin(null);
		setCape(null);
	};

	const renameItem = async (item: Item, index: number) => {
		let name: string;
		try {
			const {value} = await showModal({
				mode: 'prompt',
				text: t('user.renameClosetItem'),
				input: item.pivot.item_name,
				validator(value: string) {
					if (!value) {
						return t('skinlib.emptyNewTextureName');
					}
				},
			});
			name = value;
		} catch {
			return;
		}

		const {code, message} = await fetch.put<fetch.ResponseBody>(
			urls.user.closet.rename(item.tid),
			{name},
		);
		if (code === 0) {
			toast.success(message);
			setItems(items => {
				items[index] = {...item, pivot: {...item.pivot, item_name: name}};
				return [...items];
			});
		} else {
			toast.error(message);
		}
	};

	const removeItem = async (item: Item) => {
		const {tid} = item;
		const ok = await removeClosetItem(tid);
		if (ok) {
			setItems(items => items.filter(item => item.tid !== tid));
		}
	};

	const applyToPlayer = () => {
		if (!skin && !cape) {
			toast.info(t('user.emptySelectedTexture'));
			return;
		}

		setShowModalApply(true);
	};

	return (
		<>
			<div ref={containerReference} className='card card-primary card-tabs'>
				<div className='card-header p-0 pt-1 pl-1'>
					<div className='d-flex justify-content-between'>
						<ul className='nav nav-tabs' role='tablist'>
							<li className='nav-item'>
								<a
									href='#'
									className={`nav-link ${category === 'skin' ? 'active' : ''}`}
									data-toggle='pill'
									role='tab'
									onClick={switchCategoryToSkin}
								>
									{t('general.skin')}
								</a>
							</li>
							<li className='nav-item'>
								<a
									href='#'
									className={`nav-link ${
										category === TextureType.Cape ? 'active' : ''
									}`}
									data-toggle='pill'
									role='tab'
									onClick={switchCategoryToCape}
								>
									{t('general.cape')}
								</a>
							</li>
							<li className='nav-item d-none d-md-block'>
								<a
									href={`${blessing.base_url}/skinlib/upload`}
									className='nav-link'
								>
									{t('user.closet.upload')}
								</a>
							</li>
						</ul>
						<div className='mr-3 my-2 my-lg-0'>
							<input
								type='search'
								value={search}
								className='form-control mr-sm-2'
								aria-label='Search'
								placeholder={t('user.typeToSearch')}
								onChange={handleSearch}
							/>
						</div>
					</div>
				</div>
				<div className='card-body'>
					{isLoading ? (
						<div className='d-flex flex-wrap'>
							{new Array(perPageReference.current).fill(null).map((_, i) => (
								<LoadingClosetItem key={i}/>
							))}
						</div>
					) : (items.length === 0 ? (
						<div className='text-center p-3'>
							{search ? (
								t('general.noResult')
							) : (
								<span
									dangerouslySetInnerHTML={{
										__html: t('user.emptyClosetMsg', {
											url: `${blessing.base_url}/skinlib?filter=${category}`,
										}),
									}}
								 />
							)}
						</div>
					) : (
						<div className='d-flex flex-wrap'>
							{items.map((item, i) => (
								<ClosetItem
									key={item.tid}
									item={item}
									selected={isSelected(item)}
									onClick={handleSelect}
									onRename={async () => renameItem(item, i)}
									onRemove={async () => removeItem(item)}
								/>
							))}
						</div>
					))}
				</div>
				<div className='card-footer'>
					<div className='float-right'>
						<Pagination
							page={page}
							totalPages={totalPages}
							onChange={handlePageChange}
						/>
					</div>
				</div>
			</div>
			<Previewer
				skin={skin?.hash}
				cape={cape?.hash}
				isAlex={skin?.type === TextureType.Alex}
			>
				<div className='d-flex justify-content-between'>
					<button className='btn btn-primary' onClick={applyToPlayer}>
						{t('user.useAs')}
					</button>
					<button className='btn btn-default' onClick={resetSelected}>
						{t('user.resetSelected')}
					</button>
				</div>
			</Previewer>
			<ModalApply
				canAdd
				show={showModalApply}
				skin={skin?.tid}
				cape={cape?.tid}
				onClose={() => {
					setShowModalApply(false);
				}}
			/>
		</>
	);
}

export default Closet;
