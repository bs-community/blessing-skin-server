/** @jsxImportSource @emotion/react */
import React, {useState, useEffect} from 'react';
import Autosuggest from 'react-autosuggest';
import {css} from '@emotion/react';
import {emit} from '@/scripts/event';
import {pointerCursor} from '@/styles/utils';

const styles = css`
  .dropdown-menu li {
    ${pointerCursor}
  }
`;

const domainNames = new Set(['qq.com', '163.com', 'gmail.com', 'hotmail.com']);

type Properties = Omit<Autosuggest.InputProps<string>, 'onChange'> & {
	onChange(value: string): void;
};

const EmailSuggestion: React.FC<Properties> = properties => {
	const [suggestions, setSuggestions] = useState<string[]>([]);

	useEffect(() => {
		emit('emailDomainsSuggestion', domainNames);
	}, []);

	const handleSuggestionsFetchRequested: Autosuggest.SuggestionsFetchRequested
    = ({value}) => {
    	const segments = value.split('@');
    	setSuggestions([...domainNames].map(name => `${segments[0]}@${name}`));
    };

	const handleSuggestionsClearRequested = () => {
		setSuggestions([]);
	};

	const shouldRenderSuggestions = (value: string) => {
		const isSelecting = [...domainNames].some(name =>
			value.endsWith(`@${name}`),
		);

		return isSelecting || (value.length > 0 && !value.includes('@'));
	};

	const getSuggestionValue = (value: string) => value;

	const renderSuggestion = (suggestion: string) => suggestion;

	const handleChange = (_: React.FormEvent, event: Autosuggest.ChangeEvent) => {
		properties.onChange(event.newValue);
	};

	const renderInputComponent = (
		properties_: Omit<Autosuggest.InputProps<string>, 'onChange'>,
	) => (
		<div className='input-group'>
			<input className='form-control' {...properties_}/>
			<div className='input-group-append'>
				<div className='input-group-text'>
					<i className='fas fa-envelope'/>
				</div>
			</div>
		</div>
	);

	return (
		<div css={styles}>
			<Autosuggest
				suggestions={suggestions}
				getSuggestionValue={getSuggestionValue}
				renderSuggestion={renderSuggestion}
				shouldRenderSuggestions={shouldRenderSuggestions}
				inputProps={({...properties, onChange: handleChange})}
				renderInputComponent={renderInputComponent}
				theme={{
					container: 'mb-3',
					suggestion: 'dropdown-item',
					suggestionsContainer: 'dropdown',
					suggestionsList: `dropdown-menu ${suggestions.length > 0 ? 'show' : ''}`,
					suggestionHighlighted: 'active',
				}}
				onSuggestionsFetchRequested={handleSuggestionsFetchRequested}
				onSuggestionsClearRequested={handleSuggestionsClearRequested}
			/>
		</div>
	);
};

export default EmailSuggestion;
