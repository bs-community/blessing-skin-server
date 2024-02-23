import React, {useEffect, useRef} from 'react';
import ReactDOM from 'react-dom';
import styled from '@emotion/styled';
import {Terminal} from 'xterm';
import {FitAddon} from 'xterm-addon-fit';
import {Shell, type Stdio} from 'blessing-skin-shell';
import 'xterm/css/xterm.css';
import Draggable from 'react-draggable';
import * as event from './event';
import AptCommand from './cli/AptCommand';
import ClosetCommand from './cli/ClosetCommand';
import DnfCommand from './cli/DnfCommand';
import PacmanCommand from './cli/PacmanCommand';
import RmCommand from './cli/RmCommand';
import * as breakpoints from '@/styles/breakpoints';

let launched = false;

const TerminalContainer = styled.div`
  z-index: 1040;
  position: fixed;
  bottom: 7vh;
  user-select: none;

  .card-body {
    background-color: #000;
  }

  ${breakpoints.greaterThan(breakpoints.Breakpoint.xl)} {
    left: 25vw;
    width: 50vw;
    height: 50vh;
  }

  ${breakpoints.between(breakpoints.Breakpoint.md, breakpoints.Breakpoint.xl)} {
    left: 5vw;
    width: 90vw;
    height: 40vh;
  }

  ${breakpoints.lessThan(breakpoints.Breakpoint.md)} {
    left: 1vw;
    width: 98vw;
    height: 35vh;
  }
`;

const TerminalWindow: React.FC<{onClose(): void}> = properties => {
	const mount = useRef<HTMLDivElement>(null);

	useEffect(() => {
		const element = mount.current;
		if (!element) {
			return;
		}

		const terminal = new Terminal();
		const fitAddon = new FitAddon();
		terminal.loadAddon(fitAddon);
		terminal.options.fontFamily = 'Monaco, Consolas, "Roboto Mono", "Noto Sans", "Droid Sans Mono"';
		terminal.open(element);
		fitAddon.fit();

		const programs = new Map<string, (stdio: Stdio, arguments_: string[]) => void>();
		programs.set('apt', AptCommand);
		programs.set('closet', ClosetCommand);
		programs.set('dnf', DnfCommand);
		programs.set('pacman', PacmanCommand);
		programs.set('rm', RmCommand);
		event.emit('registerCLIPrograms', programs);

		const shell = new Shell(terminal);
		for (const [name, program] of programs.entries()) {
			shell.addExternal(name, program);
		}

		const originalLogger = console.log;
		console.log = (data: string, ...arguments_: any[]) => {
			const {stack} = new Error();
			if (stack?.includes('outputHelp')) {
				terminal.writeln(data.replaceAll('\n', '\r\n'));
			} else {
				// eslint-disable-next-line @typescript-eslint/no-unsafe-argument
				originalLogger(data, ...arguments_);
			}
		};

		const unbindData = terminal.onData(e => {
			shell.input(e);
		});
		const unbindKey = terminal.onKey(e => {
			event.emit('terminalKeyPress', e.key);
		},
		);
		launched = true;

		return () => {
			unbindData.dispose();
			unbindKey.dispose();
			shell.free();
			fitAddon.dispose();
			terminal.dispose();
			console.log = originalLogger;
			launched = false;
		};
	}, []);

	return (
		<Draggable handle='.card-header'>
			<TerminalContainer className='card'>
				<div className='card-header'>
					<div className='d-flex justify-content-between'>
						<h4 className='card-title d-flex align-items-center'>
							Blessing Skin Shell
						</h4>
						<button className='btn btn-default' onClick={properties.onClose}>
							&times;
						</button>
					</div>
				</div>
				<div ref={mount} className='card-body p-2'/>
			</TerminalContainer>
		</Draggable>
	);
};

export function launch() {
	if (launched) {
		return;
	}

	const container = document.createElement('div');
	document.body.append(container);

	const handleClose = () => {
		ReactDOM.unmountComponentAtNode(container);
		container.remove();
	};

	ReactDOM.render(<TerminalWindow onClose={handleClose}/>, container);
}
