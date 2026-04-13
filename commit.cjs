const { execSync } = require('child_process');
const repoPath = 'C:\\Users\\inkis\\Herd\\goloba-prod\\packages\\Goloba\\GolobaRMA';

const run = (cmd) => {
  console.log(`> ${cmd}`);
  console.log(execSync(cmd, { cwd: repoPath, encoding: 'utf8' }));
};

run('git add -A');
run('git commit -m "feat(rma): phase 2 changes 1+2 — mandatory image and T&C checkbox on both modals"');
console.log('Done.');
