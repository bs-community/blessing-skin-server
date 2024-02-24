import {expect, test, it} from 'vitest';
import {render, fireEvent} from '@testing-library/react';
import {t} from '@/scripts/i18n';
import Viewer, {PICTURES_COUNT} from '@/components/Viewer';

test('custom footer', () => {
	const {queryByText} = render(<Viewer isAlex={false}>footer</Viewer>);
	expect(queryByText('footer')).toBeInTheDocument();
});

describe('indicator', () => {
	it('hidden by default', () => {
		const {queryByText} = render(<Viewer skin='skin' isAlex={false}/>);
		expect(queryByText(t('general.skin'))).not.toBeInTheDocument();
	});

	it('nothing', () => {
		const {queryByText} = render(<Viewer showIndicator isAlex/>);
		expect(queryByText(t('general.skin'))).not.toBeInTheDocument();
		expect(queryByText(t('general.cape'))).not.toBeInTheDocument();
	});

	it('skin only', () => {
		const {queryByText} = render(
			<Viewer showIndicator skin='skin' isAlex={false}/>,
		);
		expect(queryByText(t('general.skin'))).toBeInTheDocument();
		expect(queryByText(t('general.cape'))).not.toBeInTheDocument();
	});

	it('cape only', () => {
		const {queryByText} = render(
			<Viewer showIndicator cape='cape' isAlex={false}/>,
		);
		expect(queryByText(t('general.skin'))).not.toBeInTheDocument();
		expect(queryByText(t('general.cape'))).toBeInTheDocument();
	});

	it('skin and cape', () => {
		const {queryByText} = render(
			<Viewer showIndicator skin='skin' cape='cape' isAlex={false}/>,
		);
		expect(
			queryByText(`${t('general.skin')} & ${t('general.cape')}`),
		).toBeInTheDocument();
		expect(queryByText(t('general.skin'))).not.toBeInTheDocument();
		expect(queryByText(t('general.cape'))).not.toBeInTheDocument();
	});
});

describe('actions', () => {
	it('toggle animation', () => {
		const component = <Viewer isAlex={false}/>;
		const {getByTitle} = render(component);
		fireEvent.click(getByTitle(`${t('general.switchAnimation')}`)); // Should start running
		fireEvent.click(getByTitle(`${t('general.switchAnimation')}`)); // Should start flying
		fireEvent.click(getByTitle(`${t('general.switchAnimation')}`)); // Should be idle
		fireEvent.click(getByTitle(`${t('general.switchAnimation')}`)); // Should start walking
	});

	it('toggle rotation', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		fireEvent.click(getByTitle(t('general.rotation'))); // Should stop rotation
		fireEvent.click(getByTitle(t('general.rotation'))); // Should start rotation
	});

	it('toggle pause', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		const icon = getByTitle(t('general.pauseAnimation'));
		fireEvent.click(icon);
		expect(icon).toHaveClass('fa-play');
	});
});

describe('background', () => {
	it('white', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		fireEvent.click(getByTitle(t('colors.white')));
	});

	it('black', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		fireEvent.click(getByTitle(t('colors.black')));
	});

	it('white', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		fireEvent.click(getByTitle(t('colors.gray')));
	});

	it('previous picture', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);
		fireEvent.click(getByTitle(t('colors.prev')));
		fireEvent.click(getByTitle(t('colors.prev')));
	});

	it('next picture', () => {
		const {getByTitle} = render(<Viewer isAlex={false}/>);

		fireEvent.click(getByTitle(t('colors.next')));
		fireEvent.click(getByTitle(t('colors.next')));

		Array.from({length: PICTURES_COUNT - 1}).forEach(() => {
			fireEvent.click(getByTitle(t('colors.next')));
		});
	});

	it('default for dark mode', () => {
		document.body.classList.add('dark-mode');

		render(<Viewer isAlex={false}/>);

		document.body.classList.remove('dark-mode');
	});
});
