import {emit} from '@/scripts/event';
import {pointerCursor} from '@/styles/utils';
import {css} from '@emotion/react';
import clsx from 'clsx';
import {useCombobox} from 'downshift';
import {useEffect, useState} from 'react';

const styles = css`
  .dropdown-menu li {
    ${pointerCursor}
  }
`;

const domainNames = new Set(['qq.com', '163.com', 'gmail.com', 'hotmail.com']);

type Props = Omit<React.InputHTMLAttributes<HTMLInputElement>, 'onChange'> & {
	onChange: (value: string) => void;
};

const EmailSuggestion: React.FC<Props> = props => {
	useEffect(() => {
		emit('emailDomainsSuggestion', domainNames);
	}, []);
	const [inputItems, setInputItems] = useState<string[]>([]);

	const {
		isOpen,
		getLabelProps,
		getMenuProps,
		getInputProps,
		highlightedIndex,
		getItemProps,
	} = useCombobox({
		items: inputItems,
		onInputValueChange({inputValue: value}) {
			setInputItems([...domainNames].map(name => `${value.split('@')[0]}@${name}`));
			if (value.length === 0 || value.includes('@')) {
				setInputItems([]);
			}

			const {onChange} = props;
			onChange(value);
		},
	});

	return (
		<div>
			<div className='input-group'>
				<input className='form-control' {...{...props, onChange: undefined}} {...getInputProps()}/>
				<div className='input-group-text' {...getLabelProps()}>
					<i className='fas fa-envelope'/>
				</div>
			</div>
			<div className='mb-3 dropdown' css={styles}>
				<ul className={clsx('dropdown-menu', isOpen && inputItems.length > 0 && 'show')} {...getMenuProps()}>
					{isOpen && inputItems.length > 0 && inputItems.map((item, index) => (
						<li key={`${item}`} className={clsx('dropdown-item', {active: index === highlightedIndex})} {...getItemProps({item, index})}>
							{item}
						</li>
					))}
				</ul>
			</div>
		</div>
	);
};

export default EmailSuggestion;
