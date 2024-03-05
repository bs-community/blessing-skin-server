import React from 'react';
import ReactDOM from 'react-dom';
import {createRoot} from 'react-dom/client';
import $ from 'jquery';
// eslint-disable-next-line import/no-unassigned-import
import './scripts/app';
import routes from './scripts/route';

// eslint-disable-next-line @typescript-eslint/naming-convention
Object.assign(window, {React, ReactDOM, $});

const route = routes.find(route =>
	new RegExp(`^${route.path}$`, 'i').test(blessing.route),
);
if (route) {
	if (route.module) {
		void Promise.all(route.module.map(async m => m()));
	}

	if (route.react) {
		const Component = React.lazy(
			route.react as () => Promise<{default: React.ComponentType}>,
		);

		const container = typeof route.el === 'string'
			? document.querySelector(route.el)
			: null;

		const root = createRoot(container!);
		root.render(
			<React.StrictMode>
				<React.Suspense fallback={route.frame?.() ?? ''}>
					<Component/>
				</React.Suspense>
			</React.StrictMode>,
		);
	}
}
