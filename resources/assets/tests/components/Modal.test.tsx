import {
	expect, test, vi, it,
} from 'vitest';
import {render, fireEvent, act} from '@testing-library/react';
import $ from 'jquery';
import {t} from '@/scripts/i18n';
import Modal from '@/components/Modal';

test('hidden by default', () => {
	const {queryByRole} = render(<Modal/>);
	expect(queryByRole('dialog')).toBeNull();
});

test('receive id', () => {
	const {getByRole} = render(<Modal show id='kumiko'/>);
	expect(getByRole('dialog')).toHaveAttribute('id', 'kumiko');
});

test('centered dialog', () => {
	const {getByRole} = render(<Modal center show/>);
	expect(getByRole('document')).toHaveClass('modal-dialog-centered');
});

test('background color', () => {
	const {container} = render(<Modal show type='primary'/>);
	expect(container.querySelector('.modal-content')).toHaveClass('bg-primary');
});

test('jQuery events', () => {
	const {getByText} = render(<Modal show mode='confirm'/>);
	act(() => {
		vi.runAllTimers();
		$('.modal').trigger('hidden.bs.modal');
	});

	fireEvent.click(getByText(t('general.cancel')));
	act(() => {
		vi.runAllTimers();
		$('.modal').trigger('hidden.bs.modal');
	});
});

describe('modal header', () => {
	it('modal title', () => {
		const {queryByText} = render(<Modal show title='Tips'/>);
		expect(queryByText('Tips')).toBeInTheDocument();
	});

	it('hide modal header', () => {
		const {queryByText} = render(
			<Modal show title='Tips' showHeader={false}/>,
		);
		expect(queryByText('Tips')).not.toBeInTheDocument();
	});
});

describe('modal body', () => {
	it('custom children', () => {
		const {queryByText} = render(<Modal show>body</Modal>);
		expect(queryByText('body')).toBeInTheDocument();
	});

	it('text with linebreaks', () => {
		const {queryByText} = render(<Modal show text={'L1\nL2'}/>);
		expect(queryByText('L1')).toBeInTheDocument();
		expect(queryByText('L2')).toBeInTheDocument();
	});

	it('dangerous HTML', () => {
		const {getByText} = render(
			<Modal show dangerousHTML="<h1 class='h1'>ab</h1>"/>,
		);
		expect(getByText('ab')).toHaveClass('h1');
	});

	describe('input control', () => {
		it('set default value', () => {
			const {queryByDisplayValue} = render(
				<Modal show mode='prompt' input='val'/>,
			);
			expect(queryByDisplayValue('val')).toBeInTheDocument();
		});

		it('placeholder', () => {
			const {queryByPlaceholderText} = render(
				<Modal show mode='prompt' placeholder='hint'/>,
			);
			expect(queryByPlaceholderText('hint')).toBeInTheDocument();
		});

		it('input control type', () => {
			const {getByPlaceholderText} = render(
				<Modal
					show
					mode='prompt'
					placeholder='password'
					inputType='password'
				/>,
			);
			expect(getByPlaceholderText('password')).toHaveAttribute(
				'type',
				'password',
			);
		});
	});
});

describe('modal footer', () => {
	it('custom footer content', () => {
		const {queryByText} = render(<Modal show footer={<div>footer</div>}/>);
		expect(queryByText('footer')).toBeInTheDocument();
		expect(queryByText(t('general.confirm'))).not.toBeInTheDocument();
	});

	it('flex footer', () => {
		const {getByText} = render(<Modal flexFooter show footer='footer'/>);
		expect(getByText('footer')).toHaveClass('d-flex', 'justify-content-between');
	});

	it('custom ok button', () => {
		const {getByText} = render(
			<Modal show okButtonType='primary' okButtonText='kumiko'/>,
		);
		expect(getByText('kumiko')).toHaveClass('btn-primary');
	});

	it('custom cancel button', () => {
		const {getByText} = render(
			<Modal show cancelButtonType='success' cancelButtonText='reina'/>,
		);
		expect(getByText('reina')).toHaveClass('btn-success');
	});
});

describe('"alert" mode', () => {
	it('buttons', () => {
		const resolve = vi.fn();
		const {getByText, queryByText} = render(
			<Modal show mode='alert' onConfirm={resolve}/>,
		);
		fireEvent.click(getByText(t('general.confirm')));
		expect(resolve).toBeCalledWith({value: ''});
		expect(queryByText(t('general.cancel'))).toBeNull();
	});

	it('confirm callback is optional', () => {
		const {getByText} = render(<Modal show mode='alert'/>);
		fireEvent.click(getByText(t('general.confirm')));
	});
});

describe('"confirm" mode', () => {
	it('default mode is "confirm"', () => {
		const {queryByText} = render(<Modal show/>);
		expect(queryByText(t('general.confirm'))).toBeInTheDocument();
		expect(queryByText(t('general.cancel'))).toBeInTheDocument();
	});

	it('"confirm" button', () => {
		const resolve = vi.fn();
		const reject = vi.fn();
		const {getByText} = render(
			<Modal show mode='prompt' onConfirm={resolve} onDismiss={reject}/>,
		);
		fireEvent.click(getByText(t('general.confirm')));
		expect(resolve).toBeCalledWith({value: ''});
		expect(reject).not.toBeCalled();
	});

	it('"cancel" button', () => {
		const resolve = vi.fn();
		const reject = vi.fn();
		const {getByText} = render(
			<Modal show mode='prompt' onConfirm={resolve} onDismiss={reject}/>,
		);
		fireEvent.click(getByText(t('general.cancel')));
		expect(resolve).not.toBeCalled();
		expect(reject).toBeCalled();
	});
});

describe('"prompt" mode', () => {
	it('retrieve input value', () => {
		const resolve = vi.fn();
		const reject = vi.fn();
		const {getByPlaceholderText, getByText} = render(
			<Modal
				show
				mode='prompt'
				placeholder='hint'
				onConfirm={resolve}
				onDismiss={reject}
			/>,
		);
		fireEvent.change(getByPlaceholderText('hint'), {
			target: {value: 'my'},
		});
		fireEvent.click(getByText(t('general.confirm')));
		expect(resolve).toBeCalledWith({value: 'my'});
		expect(reject).not.toBeCalled();
	});

	it('cancel dialog', () => {
		const resolve = vi.fn();
		const reject = vi.fn();
		const {getByText} = render(
			<Modal show mode='prompt' onConfirm={resolve} onDismiss={reject}/>,
		);
		fireEvent.click(getByText(t('general.cancel')));
		expect(resolve).not.toBeCalled();
		expect(reject).toBeCalled();
	});

	it('radio inputs', () => {
		const choices = [
			{text: 'A', value: 'a'},
			{text: 'B', value: 'b'},
		];
		const resolve = vi.fn();
		const {getByText, getByLabelText} = render(
			<Modal
				show
				mode='prompt'
				inputType='radios'
				choices={choices}
				onConfirm={resolve}
			/>,
		);
		fireEvent.click(getByLabelText('B'));
		fireEvent.click(getByText(t('general.confirm')));
		expect(resolve).toBeCalledWith({value: 'b'});
	});

	it('validate input', () => {
		const resolve = vi.fn();
		const reject = vi.fn();
		const validator = vi.fn().mockReturnValue(true);
		const {getByText} = render(
			<Modal
				show
				mode='prompt'
				input='val'
				validator={validator}
				onConfirm={resolve}
				onDismiss={reject}
			/>,
		);
		fireEvent.click(getByText(t('general.confirm')));
		expect(resolve).toBeCalledWith({value: 'val'});
		expect(reject).not.toBeCalled();
	});

	it('report validator message', () => {
		const message = 'Invalid input.';
		const resolve = vi.fn();
		const reject = vi.fn();
		const validator = vi.fn().mockReturnValue(message);
		const {getByText, queryByText} = render(
			<Modal
				show
				mode='prompt'
				input='val'
				validator={validator}
				onConfirm={resolve}
				onDismiss={reject}
			/>,
		);
		expect(queryByText(message)).not.toBeInTheDocument();

		fireEvent.click(getByText(t('general.confirm')));
		expect(queryByText(message)).toBeInTheDocument();
		expect(resolve).not.toBeCalled();
		expect(reject).not.toBeCalled();
	});
});

describe('"onClose" event', () => {
	it('button confirm', () => {
		const mock = vi.fn();
		const {getByText} = render(<Modal show mode='confirm' onClose={mock}/>);
		fireEvent.click(getByText(t('general.confirm')));
		expect(mock).toBeCalled();
	});

	it('button cancel', () => {
		const mock = vi.fn();
		const {getByText} = render(<Modal show mode='confirm' onClose={mock}/>);
		fireEvent.click(getByText(t('general.cancel')));
		expect(mock).toBeCalled();
	});
});
