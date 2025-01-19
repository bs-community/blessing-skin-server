import {Tooltip} from 'bootstrap';
import '@popperjs/core';
import 'admin-lte';
import './extra';
import './i18n';
import './net';
import './event';
import './notification';
import './emailVerification';
import './logout';
import './darkMode';

window.addEventListener('load', () => {
	[...document.querySelectorAll('[data-toggle="tooltip"]')].map(el => new Tooltip(el));
});
