const { execSync } = require('child_process');
const repoPath = 'C:\\Users\\inkis\\Herd\\goloba-prod\\packages\\Goloba\\GolobaRMA';

const run = (cmd) => {
  console.log(`> ${cmd}`);
  console.log(execSync(cmd, { cwd: repoPath, encoding: 'utf8' }));
};

run('git add -A');
run('git commit -m "feat(rma): change 3 — retracto forces return resolution, translate resolution labels to Spanish"');
console.log('Done.');
