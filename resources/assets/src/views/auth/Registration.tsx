import React, { useState, useRef } from 'react'
import { hot } from 'react-hot-loader/root'
import useBlessingExtra from '@/scripts/hooks/useBlessingExtra'
import useEmitMounted from '@/scripts/hooks/useEmitMounted'
import { t } from '@/scripts/i18n'
import * as fetch from '@/scripts/net'
import { toast } from '@/scripts/notify'
import urls from '@/scripts/urls'
import Alert from '@/components/Alert'
import Captcha from '@/components/Captcha'
import EmailSuggestion from '@/components/EmailSuggestion'

const Registration: React.FC = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [confirmation, setConfirmation] = useState('')
  const [nickName, setNickName] = useState('')
  const [playerName, setPlayerName] = useState('')
  const [isPending, setIsPending] = useState(false)
  const [warningMessage, setWarningMessage] = useState('')
  const requirePlayer = useBlessingExtra<boolean>('player')
  const confirmationRef = useRef<HTMLInputElement | null>(null)
  const captchaRef = useRef<Captcha | null>(null)

  useEmitMounted()

  const handlePasswordChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setPassword(event.target.value)
  }

  const handleConfirmationChange = (
    event: React.ChangeEvent<HTMLInputElement>,
  ) => {
    setConfirmation(event.target.value)
  }

  const handleNickNameChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setNickName(event.target.value)
  }

  const handlePlayerNameChange = (
    event: React.ChangeEvent<HTMLInputElement>,
  ) => {
    setPlayerName(event.target.value)
  }

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setWarningMessage('')

    if (password !== confirmation) {
      setWarningMessage(t('auth.invalidConfirmPwd'))
      confirmationRef.current!.focus()
      return
    }

    setIsPending(true)
    const { code, message } = await fetch.post<fetch.ResponseBody>(
      urls.auth.register(),
      Object.assign(
        { email, password, captcha: await captchaRef.current!.execute() },
        requirePlayer ? { player_name: playerName } : { nickname: nickName },
      ),
    )
    if (code === 0) {
      toast.success(message)
      setTimeout(() => {
        window.location.href = `${blessing.base_url}/user`
      }, 3000)
    } else {
      setWarningMessage(message)
      captchaRef.current!.reset()
    }
    setIsPending(false)
  }

  return (
    <form onSubmit={handleSubmit}>
      <EmailSuggestion
        type="email"
        required
        autoFocus
        placeholder={t('auth.email')}
        value={email}
        onChange={setEmail}
      />
      <div className="input-group mb-3">
        <input
          type="password"
          required
          minLength={8}
          maxLength={32}
          className="form-control"
          placeholder={t('auth.password')}
          autoComplete="new-password"
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
          ref={confirmationRef}
          value={confirmation}
          onChange={handleConfirmationChange}
        />
        <div className="input-group-append">
          <div className="input-group-text">
            <i className="fas fa-sign-in-alt"></i>
          </div>
        </div>
      </div>
      {requirePlayer ? (
        <div className="input-group mb-3" title={t('auth.player-name-intro')}>
          <input
            type="text"
            required
            className="form-control"
            placeholder={t('auth.player-name')}
            value={playerName}
            onChange={handlePlayerNameChange}
          />
          <div className="input-group-append">
            <div className="input-group-text">
              <i className="fas fa-gamepad"></i>
            </div>
          </div>
        </div>
      ) : (
        <div className="input-group mb-3" title={t('auth.nickname-intro')}>
          <input
            type="text"
            required
            className="form-control"
            placeholder={t('auth.nickname')}
            value={nickName}
            onChange={handleNickNameChange}
          />
          <div className="input-group-append">
            <div className="input-group-text">
              <i className="fas fa-gamepad"></i>
            </div>
          </div>
        </div>
      )}
      <Captcha ref={captchaRef} />

      <Alert type="warning">{warningMessage}</Alert>

      <div className="d-flex justify-content-between align-items-center mb-3">
        <a href={`${blessing.base_url}/auth/login`}>{t('auth.login-link')}</a>
        <button className="btn btn-primary" type="submit" disabled={isPending}>
          {isPending ? (
            <>
              <i className="fas fa-spinner fa-spin mr-1"></i>
              {t('auth.registering')}
            </>
          ) : (
            t('auth.register')
          )}
        </button>
      </div>
    </form>
  )
}

export default hot(Registration)
