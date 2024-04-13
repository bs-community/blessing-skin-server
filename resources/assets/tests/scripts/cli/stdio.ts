export class Stdio {
	private stdout = '';

	public print(data: string) {
		this.stdout += data;
	}

	public println(data: string) {
		this.stdout += data;
		this.stdout += '\r\n';
	}

	public reset() {
		//
	}

	public free() {
		//
	}

	public getStdout() {
		return this.stdout;
	}
}
