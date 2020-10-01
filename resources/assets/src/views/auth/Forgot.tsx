import React, { useState, useRef } from 'react'
import { hot } from 'react-hot-loader/root'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import urls from '@/scripts/urls'
import Alert from '@/components/Alert'
import Captcha from '@/components/Captcha'
import EmailSuggestion from '@/components/EmailSuggestion'

const Forgot: React.FC = () => {
  const [email, setEmail] = useState('')
  const [isSending, setIsSending] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')
  const [warningMessage, setWarningMessage] = useState('')
  const ref = useRef<Captcha | null>(null)

  useEmitMounted()

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setWarningMessage('')
    setIsSending(true)

    const captcha = await ref.current!.execute()
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      urls.auth.forgot(),
      { email, captcha },
    )
    if (code === 0) {
      setSuccessMessage(message)
    } else {
      setWarningMessage(message)
      ref.current!.reset()
    }
    setIsSending(false)
  }

  return (
    <form onSubmit={handleSubmit}>
      <EmailSuggestion
        type="email"
        placeholder={t('auth.email')}
        required
        autoFocus
        value={email}
        onChange={setEmail}
      />

      <Captcha ref={ref} />

      <Alert type="success">{successMessage}</Alert>
      <Alert type="warning">{warningMessage}</Alert>

      <div className="d-flex justify-content-between align-items-center">
        <a href={`${blessing.base_url}/auth/login`}>
          {t('auth.forgot.login-link')}
        </a>
        <button className="btn btn-primary" type="submit" disabled={isSending}>
          {isSending ? (
            <>
              <i className="fas fa-spinner fa-spin mr-1" />
              {t('auth.sending')}
            </>
          ) : (
            t('auth.send')
          )}
        </button>
      </div>
    </form>
  )
}

export default hot(Forgot)
