import type {ReaptchaProps} from 'reaptcha';
import React from 'react';

class Reaptcha extends React.Component<ReaptchaProps, {}> {
	execute() {
		this.props.onVerify('token');
	}

	reset() {}

	render() {
		return <></>;
	}
}

export default Reaptcha;
