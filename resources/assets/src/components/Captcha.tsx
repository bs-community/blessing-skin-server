/** @jsxImportSource @emotion/react */
import * as React from 'react'
import Reaptcha from 'reaptcha'
import { emit, on } from '@/scripts/event'
import { t } from '@/scripts/i18n'
import * as cssUtils from '@/styles/utils'

const eventId = Symbol()

type State = {
  value: string
  time: number
  sitekey: string
  invisible: boolean
}

class Captcha extends React.Component<Record<string, unknown>, State> {
  state: State
  ref: React.MutableRefObject<Reaptcha | null>

  constructor(props: Record<string, unknown>) {
    super(props)
    this.state = {
      value: '',
      time: Date.now(),
      sitekey: blessing.extra.recaptcha,
      invisible: blessing.extra.invisible,
    }
    this.ref = React.createRef()
  }

  execute = async () => {
    const recaptcha = this.ref.current
    if (recaptcha && this.state.invisible) {
      return new Promise<string>((resolve) => {
        const off = on(eventId, (value: string) => {
          resolve(value)
          off()
        })
        recaptcha.execute()
      })
    }
    return this.state.value
  }

  reset = () => {
    const recaptcha = this.ref.current
    if (recaptcha) {
      recaptcha.reset()
    } else {
      this.setState({ time: Date.now() })
    }
  }

  handleValueChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.setState({ value: event.target.value })
  }

  handleVerify = (value: string) => {
    emit(eventId, value)
    this.setState({ value })
  }

  handleRefresh = () => {
    this.setState({ time: Date.now() })
  }

  render() {
    return this.state.sitekey ? (
      <div className="mb-2">
        <Reaptcha
          ref={this.ref}
          sitekey={this.state.sitekey}
          size={this.state.invisible ? 'invisible' : 'normal'}
          onVerify={this.handleVerify}
        />
      </div>
    ) : (
      <div className="d-flex">
        <div className="form-group mb-3 mr-2">
          <input
            type="text"
            className="form-control"
            placeholder={t('auth.captcha')}
            required
            value={this.state.value}
            onChange={this.handleValueChange}
          />
        </div>
        <img
          src={`${blessing.base_url}/auth/captcha?v=${this.state.time}`}
          alt={t('auth.captcha')}
          css={cssUtils.pointerCursor}
          height={34}
          title={t('auth.change-captcha')}
          onClick={this.handleRefresh}
        />
      </div>
    )
  }
}

export default Captcha
