const { execSync } = require('child_process');
execSync('git add -A', { stdio: 'inherit' });
execSync('git commit -m "fix(rma): seller index status badges use hex colors from rma_status table"', { stdio: 'inherit' });
