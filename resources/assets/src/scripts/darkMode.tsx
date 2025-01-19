import DarkModeButton from '@/components/DarkModeButton';
import ReactDOM from 'react-dom';

const element = document.querySelector('#toggle-dark-mode');
if (element) {
	const initMode = document.body.classList.contains('dark-mode');
	ReactDOM.render(<DarkModeButton initMode={initMode}/>, element);
}
