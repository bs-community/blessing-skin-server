import ReactDOM from 'react-dom';
import DarkModeButton from '@/components/DarkModeButton';

const element = document.querySelector('#toggle-dark-mode');
if (element) {
	const initMode = document.body.classList.contains('dark-mode');
	ReactDOM.render(<DarkModeButton initMode={initMode}/>, element);
}
