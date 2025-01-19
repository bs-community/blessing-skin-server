import NotificationsList from '@/views/widgets/NotificationsList';
import {createRoot} from 'react-dom/client';

const container = document.querySelector('[data-notifications]');
if (container) {
	createRoot(container).render(<NotificationsList/>);
}
