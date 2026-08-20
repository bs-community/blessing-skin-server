import React from 'react'
import type { Props } from 'reaptcha'

class Reaptcha extends React.Component<Props, {}> {
  execute() {
    this.props.onVerify('token')
  }

  reset() {}

  render() {
    return <></>
  }
}

export default Reaptcha
