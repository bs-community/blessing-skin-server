import ReactDOM from 'react-dom';
import NotificationsList from '@/views/widgets/NotificationsList';

const container = document.querySelector('[data-notifications]');
if (container) {
	ReactDOM.render(<NotificationsList/>, container);
}
