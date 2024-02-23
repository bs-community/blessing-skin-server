import {useState, useRef} from 'react';
import useEmitMounted from '@/scripts/hooks/useEmitMounted';
import {t} from '@/scripts/i18n';
import * as fetch from '@/scripts/net';
import urls from '@/scripts/urls';
import Alert from '@/components/Alert';
import Captcha from '@/components/Captcha';
import EmailSuggestion from '@/components/EmailSuggestion';

export default function Forgot() {
	const [email, setEmail] = useState('');
	const [isSending, setIsSending] = useState(false);
	const [successMessage, setSuccessMessage] = useState('');
	const [warningMessage, setWarningMessage] = useState('');
	const reference = useRef<Captcha | null>(null);

	useEmitMounted();

	const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
		event.preventDefault();
		setWarningMessage('');
		setIsSending(true);

		const captcha = await reference.current!.execute();
		const {code, message} = await fetch.post<fetch.ResponseBody>(
			urls.auth.forgot(),
			{email, captcha},
		);
		if (code === 0) {
			setSuccessMessage(message);
		} else {
			setWarningMessage(message);
			reference.current!.reset();
		}

		setIsSending(false);
	};

	return (
		<form onSubmit={handleSubmit}>
			<EmailSuggestion
				required
				autoFocus
				type='email'
				placeholder={t('auth.email')}
				value={email}
				onChange={setEmail}
			/>

			<Captcha ref={reference}/>

			<Alert type='success'>{successMessage}</Alert>
			<Alert type='warning'>{warningMessage}</Alert>

			<div className='d-flex justify-content-between align-items-center'>
				<a href={`${blessing.base_url}/auth/login`}>
					{t('auth.forgot.login-link')}
				</a>
				<button className='btn btn-primary' type='submit' disabled={isSending}>
					{isSending ? (
						<>
							<i className='fas fa-spinner fa-spin mr-1'/>
							{t('auth.sending')}
						</>
					) : (
						t('auth.send')
					)}
				</button>
			</div>
		</form>
	);
}
