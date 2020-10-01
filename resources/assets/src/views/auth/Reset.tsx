import React, { useState } from 'react'
import { hot } from 'react-hot-loader/root'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast } from '@/scripts/notify'
import urls from '@/scripts/urls'
import Alert from '@/components/Alert'

const Reset: React.FC = () => {
  const [password, setPassword] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [warningMessage, setWarningMessage] = useState('')
  const [isPending, setIsPending] = useState(false)

  useEmitMounted()

  const handlePasswordChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setPassword(event.target.value)
  }

  const handleConfirmationChange = (
    event: React.ChangeEvent<HTMLInputElement>,
  ) => {
    setConfirmation(event.target.value)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    if (password !== confirmation) {
      setWarningMessage(t('auth.invalidConfirmPwd'))
      return
    }

    setIsPending(true)
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      location.href.replace(blessing.base_url, ''),
      { password },
    )
    if (code === 0) {
      toast.success(message)
      setTimeout(() => {
        window.location.href = blessing.base_url + urls.auth.login()
      }, 2000)
    } else {
      setWarningMessage(message)
      setIsPending(false)
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      <div className="input-group mb-3">
        <input
          type="password"
          required
          autoFocus
          minLength={8}
          maxLength={32}
          className="form-control"
          placeholder={t('auth.password')}
          value={password}
          onChange={handlePasswordChange}
        />
        <div className="input-group-append">
          <div className="input-group-text">
            <i className="fas fa-lock"></i>
          </div>
        </div>
      </div>
      <div className="input-group mb-3">
        <input
          type="password"
          required
          minLength={8}
          maxLength={32}
          className="form-control"
          placeholder={t('auth.repeat-pwd')}
          autoComplete="new-password"
          value={confirmation}
          onChange={handleConfirmationChange}
        />
        <div className="input-group-append">
          <div className="input-group-text">
            <i className="fas fa-sign-in-alt"></i>
          </div>
        </div>
      </div>

      <Alert type="warning">{warningMessage}</Alert>

      <button
        className="btn btn-primary float-right"
        type="submit"
        disabled={isPending}
      >
        {isPending ? (
          <>
            <i className="fas fa-spinner fa-spin mr-1"></i>
            {t('auth.resetting')}
          </>
        ) : (
          t('auth.reset')
        )}
      </button>
    </form>
  )
}

export default hot(Reset)
