import type {ModalOptions, ModalResult} from './components/Modal';
import type {Toast} from './scripts/toast';

declare global {
	let blessing: {
		base_url: string;
		debug: boolean;
		env: string;
		locale: string;
		site_name: string;
		version: string;
		route: string;
		extra: Record<string, unknown>;
		i18n: Record<string, unknown>;

		fetch: {
			get: (url: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
			post: (url: string, data?: Record<string, unknown>) => Promise<Record<string, unknown>>;
			put: (url: string, data?: Record<string, unknown>) => Promise<Record<string, unknown>>;
			del: (url: string, data?: Record<string, unknown>) => Promise<Record<string, unknown>>;
		};

		event: {
			on: (eventName: string, listener: (...args: any[]) => void) => void;
			emit: (eventName: string, payload: Record<string, unknown>) => void;
		};

		notify: {
			showModal: (options?: ModalOptions) => Promise<ModalResult>;
			toast: Toast;
		};
	};
}
