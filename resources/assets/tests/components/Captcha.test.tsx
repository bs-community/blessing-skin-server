import React from 'react'
import { render, fireEvent } from '@testing-library/react'
import Reaptcha from 'reaptcha'
import { t } from '@/scripts/i18n'
import Captcha from '@/components/Captcha'

describe('picture captcha', () => {
  it('retrieve value', async () => {
    const ref = React.createRef<Captcha>()
    const { getByPlaceholderText } = render(<Captcha ref={ref} />)

    fireEvent.input(getByPlaceholderText(t('auth.captcha')), {
      target: { value: 'abc' },
    })
    expect(await ref.current?.execute()).toBe('abc')
  })

  it('refresh on click', () => {
    const spy = jest.spyOn(Date, 'now')

    const ref = React.createRef<Captcha>()
    const { getByAltText } = render(<Captcha ref={ref} />)

    fireEvent.click(getByAltText(t('auth.captcha')))
    expect(spy).toBeCalled()
  })

  it('refresh programatically', () => {
    const spy = jest.spyOn(Date, 'now')

    const ref = React.createRef<Captcha>()
    render(<Captcha ref={ref} />)

    ref.current?.reset()
    expect(spy).toBeCalled()
  })
})

describe('recaptcha', () => {
  beforeEach(() => {
    window.blessing.extra = { recaptcha: 'sitekey', invisible: false }
  })

  it('retrieve value', async () => {
    window.blessing.extra.invisible = true
    const spy = jest.spyOn(Reaptcha.prototype, 'execute')

    const ref = React.createRef<Captcha>()
    render(<Captcha ref={ref} />)

    const value = await ref.current?.execute()
    expect(spy).toBeCalled()
    expect(value).toBe('token')
  })

  it('refresh programatically', () => {
    const spy = jest.spyOn(Reaptcha.prototype, 'reset')

    const ref = React.createRef<Captcha>()
    render(<Captcha ref={ref} />)

    ref.current?.reset()
    expect(spy).toBeCalled()
  })
})
