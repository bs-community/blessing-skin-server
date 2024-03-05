import {useState} from 'react';
import * as fetch from '@/scripts/net';

type Properties = {
	readonly initMode: boolean;
};

const DarkModeButton: React.FC<Properties> = ({initMode}) => {
	const [darkMode, setDarkMode] = useState(initMode);

	const icon = darkMode ? 'moon' : 'sun';

	const handleClick = async () => {
		setDarkMode(value => !value);

		await fetch.put('/user/dark-mode');
		document.body.classList.toggle('dark-mode');
	};

	return (
		<a className='nav-link' href='#' role='button' onClick={handleClick}>
			<i className={`fas fa-${icon}`}/>
		</a>
	);
};

export default DarkModeButton;
