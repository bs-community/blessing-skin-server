import React, {useState, useEffect} from 'react';
import {useImmer} from 'use-immer';
import type {Report, Status} from './types';
import ImageBox from './ImageBox';
import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import {type Paginator, type Texture, TextureType} from '@/scripts/types';
import {toast, showModal} from '@/scripts/notify';
import Loading from '@/components/Loading';
import Pagination from '@/components/Pagination';
import ViewerSkeleton from '@/components/ViewerSkeleton';

const Previewer = React.lazy(async () => import('@/components/Viewer'));

function ReportsManagement() {
	const [reports, setReports] = useImmer<Report[]>([]);
	const [page, setPage] = useState(1);
	const [totalPages, setTotalPages] = useState(1);
	const [isLoading, setIsLoading] = useState(true);
	const [query, setQuery] = useState('status:0 sort:-report_at');
	const [viewingTexture, setViewingTexture] = useState<Texture | undefined>(null);

	const getReports = async () => {
		setIsLoading(true);
		const {data, last_page}: Paginator<Report> = await fetch.get(
			'/admin/reports/list',
			{
				q: query,
				page: page.toString(),
			},
		);
		setTotalPages(last_page);
		setReports(() => data);
		setIsLoading(false);
	};

	useEffect(() => {
		getReports();
	}, [page]);

	const handleQueryChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		setQuery(event.target.value);
	};

	const handleSubmitQuery = (event: React.FormEvent) => {
		event.preventDefault();
		getReports();
	};

	const handleProceedReport = async (
		report: Report,
		index: number,
		action: 'ban' | 'delete' | 'reject',
	) => {
    type Ok = {code: 0; message: string; data: {status: Status}};
    type Error_ = {code: 1; message: string};
    const resp = await fetch.put<Ok | Error_>(`/admin/reports/${report.id}`, {
    	action,
    });

    if (resp.code === 0) {
    	toast.success(resp.message);
    	setReports(reports => {
    		reports[index].status = resp.data.status;
    	});
    } else {
    	toast.error(resp.message);
    }
	};

	const handleDelete = async (report: Report, index: number) => {
		try {
			await showModal({
				text: t('skinlib.deleteNotice'),
				okButtonType: 'danger',
			});
		} catch {
			return;
		}

		handleProceedReport(report, index, 'delete');
	};

	const textureUrl
    = viewingTexture && `${blessing.base_url}/textures/${viewingTexture.hash}`;

	return (
		<div className='row'>
			<div className='col-lg-8'>
				<div className='card'>
					<div className='card-header'>
						<form className='input-group' onSubmit={handleSubmitQuery}>
							<input
								type='text'
								className='form-control'
								title={t('vendor.datatable.search')}
								value={query}
								onChange={handleQueryChange}
							/>
							<div className='input-group-append'>
								<button className='btn btn-primary' type='submit'>
									{t('vendor.datatable.search')}
								</button>
							</div>
						</form>
					</div>
					{isLoading ? (
						<div className='card-body'>
							<Loading/>
						</div>
					) : (reports.length === 0 ? (
						<div className='card-body text-center'>{t('general.noResult')}</div>
					) : (
						<div className='card-body d-flex flex-wrap'>
							{reports.map((report, i) => (
								<ImageBox
									key={report.id}
									report={report}
									onClick={setViewingTexture}
									onBan={async () => handleProceedReport(report, i, 'ban')}
									onDelete={async () => handleDelete(report, i)}
									onReject={async () => handleProceedReport(report, i, 'reject')}
								/>
							))}
						</div>
					))}
					<div className='card-footer'>
						<div className='float-right'>
							<Pagination
								page={page}
								totalPages={totalPages}
								onChange={setPage}
							/>
						</div>
					</div>
				</div>
			</div>
			<div className='col-lg-4'>
				<React.Suspense fallback={<ViewerSkeleton/>}>
					<Previewer
						{...{
							[viewingTexture?.type === TextureType.Cape
								? TextureType.Cape
								: 'skin']: textureUrl,
						}}
						isAlex={viewingTexture?.type === TextureType.Alex}
					/>
				</React.Suspense>
			</div>
		</div>
	);
}

export default ReportsManagement;
