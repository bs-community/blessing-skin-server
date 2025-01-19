import {nanoid} from 'nanoid';
import React, {useEffect, useState} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ToastBox, {type ToastType} from '../components/Toast';
import * as emitter from './event';

type QueueElement = {id: string; type: ToastType; message: string};
type ToastQueue = QueueElement[];

const ToastEvent = Symbol('toast');
const ClearEvent = Symbol('clear');

export function ToastContainer() {
	const [queue, setQueue] = useState<ToastQueue>([]);

	const handleClose = (id: string) => {
		setQueue(queue => queue.filter(element => element.id !== id));
	};

	useEffect(() => {
		const off1 = emitter.on(ToastEvent, (toast: QueueElement) => {
			setQueue(queue => {
				queue.push(toast);
				return [...queue];
			});

			// Effect dependency is empty
			// eslint-disable-next-line react-web-api/no-leaked-timeout
			setTimeout(() => {
				handleClose(toast.id);
			}, 3100);
		});
		const off2 = emitter.on(ClearEvent, () => {
			setQueue([]);
		});

		return () => {
			off1();
			off2();
		};
	}, []);

	return (
		<>
			{queue.map((element, i) => (
				<ToastBox
					key={element.id}
					type={element.type}
					distance={50 + (i * 70)}
					onClose={() => {
						handleClose(element.id);
					}}
				>
					{element.message}
				</ToastBox>
			))}
		</>
	);
}

export class Toast {
	private readonly container: HTMLDivElement;
	private readonly root: Root;

	constructor(render?: (element: React.JSX.Element) => void) {
		this.container = document.createElement('div');
		document.body.append(this.container);
		this.root = createRoot(this.container);

		if (render) {
			render(<ToastContainer/>);
		} else {
			this.root.render(<ToastContainer/>);
		}
	}

	success(message: string) {
		emitter.emit(ToastEvent, {id: nanoid(4), type: 'success', message});
	}

	info(message: string) {
		emitter.emit(ToastEvent, {id: nanoid(4), type: 'info', message});
	}

	warning(message: string) {
		emitter.emit(ToastEvent, {id: nanoid(4), type: 'warning', message});
	}

	error(message: string) {
		emitter.emit(ToastEvent, {id: nanoid(4), type: 'error', message});
	}

	clear() {
		emitter.emit(ClearEvent);
	}

	dispose() {
		this.root.unmount();
		this.container.remove();
	}
}
