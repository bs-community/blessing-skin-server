import React from 'react'
import type { ReaptchaProps } from 'reaptcha'

class Reaptcha extends React.Component<ReaptchaProps, {}> {
  execute() {
    this.props.onVerify('token')
  }

  reset() {}

  render() {
    return <></>
  }
}

export default Reaptcha
