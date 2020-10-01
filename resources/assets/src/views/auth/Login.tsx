import React, { useState, useRef, useEffect } from 'react'
import { hot } from 'react-hot-loader/root'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { showModal } from '@/scripts/notify'
import urls from '@/scripts/urls'
import Alert from '@/components/Alert'
import Captcha from '@/components/Captcha'
import EmailSuggestion from '@/components/EmailSuggestion'

type SuccessfulResponse = {
  code: 0
  message: string
  data: { redirectTo: string }
}
type FailedResponse = {
  code: number
  message: string
  data: { login_fails: number }
}
type Response = SuccessfulResponse | FailedResponse

function isSuccessfulResponse(
  response: Response,
): response is SuccessfulResponse {
  return response.code === 0
}

const Login: React.FC = () => {
  const [identification, setIdentification] = useState('')
  const [password, setPassword] = useState('')
  const [remember, setRemember] = useState(false)
  const [hasTooManyFails, setHasTooManyFails] = useState(false)
  const [isPending, setIsPending] = useState(false)
  const [warningMessage, setWarningMessage] = useState('')
  const ref = useRef<Captcha | null>(null)
  const recaptcha = useBlessingExtra<string>('recaptcha')
  const invisibleRecaptcha = useBlessingExtra<boolean>('invisible')

  useEmitMounted()

  useEffect(() => {
    setHasTooManyFails(blessing.extra.tooManyFails as boolean)
  }, [])

  const handlePasswordChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setPassword(event.target.value)
  }

  const handleRememberChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setRemember(event.target.checked)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setIsPending(true)

    const response = await fetch.post<Response>(urls.auth.login(), {
      identification,
      password,
      keep: remember,
      captcha: hasTooManyFails ? await ref.current!.execute() : undefined,
    })

    if (isSuccessfulResponse(response)) {
      window.location.href = response.data.redirectTo
    } else {
      setWarningMessage(response.message)
      setIsPending(false)
      ref.current?.reset()

      // only notify user if he/she fails too much at the first time
      if (response.data.login_fails > 3 && !hasTooManyFails) {
        setHasTooManyFails(true)
        if (recaptcha) {
          // no need to notify if using invisible recaptcha
          if (!invisibleRecaptcha) {
            showModal({
              mode: 'alert',
              text: t('auth.tooManyFails.recaptcha'),
            })
          }
        } else {
          showModal({
            mode: 'alert',
            text: t('auth.tooManyFails.captcha'),
          })
        }
      }
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      <EmailSuggestion
        type="text"
        placeholder={t('auth.identification')}
        required
        autoFocus
        value={identification}
        onChange={setIdentification}
      />
      <div className="input-group mb-3">
        <input
          type="password"
          className="form-control"
          placeholder={t('auth.password')}
          autoComplete="current-password"
          value={password}
          onChange={handlePasswordChange}
          required
        />
        <div className="input-group-append">
          <div className="input-group-text">
            <i className="fas fa-lock"></i>
          </div>
        </div>
      </div>

      {hasTooManyFails && <Captcha ref={ref} />}

      <Alert type="warning">{warningMessage}</Alert>

      <div className="d-flex justify-content-between mb-3">
        <label>
          <input
            type="checkbox"
            className="mr-1"
            checked={remember}
            onChange={handleRememberChange}
          />
          {t('auth.keep')}
        </label>
        <a href={`${blessing.base_url}/auth/forgot`}>{t('auth.forgot-link')}</a>
      </div>

      <button
        className="btn btn-primary btn-block"
        type="submit"
        disabled={isPending}
      >
        {isPending ? (
          <>
            <i className="fas fa-spinner fa-spin mr-1"></i>
            {t('auth.loggingIn')}
          </>
        ) : (
          t('auth.login')
        )}
      </button>
    </form>
  )
}

export default hot(Login)
