import EmailVerification from '@/views/widgets/EmailVerification';
import ReactDOM from 'react-dom';

const container = document.querySelector('#email-verification');

if (blessing.extra.unverified && container) {
	ReactDOM.render(<EmailVerification/>, container);
}
