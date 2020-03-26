import React, { useState, useRef } from 'react'
import { hot } from 'react-hot-loader/root'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import Alert from '@/components/Alert'
import Captcha from '@/components/Captcha'

const Forgot: React.FC = () => {
  const [email, setEmail] = useState('')
  const [isSending, setIsSending] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')
  const [warningMessage, setWarningMessage] = useState('')
  const ref = useRef<Captcha | null>(null)

  const handleEmailChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setEmail(event.target.value)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setWarningMessage('')
    setIsSending(true)

    const captcha = await ref.current!.execute()
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      '/auth/forgot',
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
      <div className="input-group mb-3">
        <input
          type="email"
          className="form-control"
          placeholder={t('auth.email')}
          required
          value={email}
          onChange={handleEmailChange}
        />
        <div className="input-group-append">
          <div className="input-group-text">
            <i className="fas fa-envelope"></i>
          </div>
        </div>
      </div>

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
            t('auth.forgot.button')
          )}
        </button>
      </div>
    </form>
  )
}

export default hot(Forgot)
