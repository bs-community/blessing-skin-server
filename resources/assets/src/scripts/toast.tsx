import React, {useState, useEffect} from 'react';
import ReactDOM from 'react-dom';
import {nanoid} from 'nanoid';
import ToastBox, {type ToastType} from '../components/Toast';
import * as emitter from './event';

type QueueElement = {id: string; type: ToastType; message: string};
type ToastQueue = QueueElement[];

const TOAST_EVENT = Symbol('toast');
const CLEAR_EVENT = Symbol('clear');

export function ToastContainer() {
	const [queue, setQueue] = useState<ToastQueue>([]);

	const handleClose = (id: string) => {
		setQueue(queue => queue.filter(element => element.id !== id));
	};

	useEffect(() => {
		const off1 = emitter.on(TOAST_EVENT, (toast: QueueElement) => {
			setQueue(queue => {
				queue.push(toast);
				return [...queue];
			});

			setTimeout(() => {
				handleClose(toast.id);
			}, 3100);
		});
		const off2 = emitter.on(CLEAR_EVENT, () => {
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
					distance={50 + i * 70}
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

	constructor(render?: (element: JSX.Element) => void) {
		this.container = document.createElement('div');
		document.body.append(this.container);

		if (render) {
			render(<ToastContainer/>);
		} else {
			ReactDOM.render(<ToastContainer/>, this.container);
		}
	}

	success(message: string) {
		emitter.emit(TOAST_EVENT, {id: nanoid(4), type: 'success', message});
	}

	info(message: string) {
		emitter.emit(TOAST_EVENT, {id: nanoid(4), type: 'info', message});
	}

	warning(message: string) {
		emitter.emit(TOAST_EVENT, {id: nanoid(4), type: 'warning', message});
	}

	error(message: string) {
		emitter.emit(TOAST_EVENT, {id: nanoid(4), type: 'error', message});
	}

	clear() {
		emitter.emit(CLEAR_EVENT);
	}

	dispose() {
		ReactDOM.unmountComponentAtNode(this.container);
		this.container.remove();
	}
}
