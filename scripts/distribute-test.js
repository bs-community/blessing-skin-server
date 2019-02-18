const execa = require('execa');

const TOTAL_AGENTS = +process.env.SYSTEM_TOTALJOBSINPHASE || 1;
const AGENT_NUMBER = +process.env.SYSTEM_JOBPOSITIONINPHASE || 1;

const tests = execa
    .sync('./vendor/bin/phpunit', ['--list-tests'])
    .stdout
    .split(/\r?\n/)
    .filter(line => line.startsWith(' - Tests'))
    .map(line => line.replace(' - Tests\\', ''));

const toBeRun = [];
for (let i = AGENT_NUMBER, length = tests.length; i <= length; i = i + TOTAL_AGENTS) {
    toBeRun.push(test[i - 1]);
}

execa('./vendor/bin/phpunit', [
    `--filter='(${toBeRun.join('|')})'`,
    `--log-junit=./junitReports/junit_${AGENT_NUMBER}.xml`
]).stdout.pipe(process.stdout);
