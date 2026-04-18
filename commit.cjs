const { execSync } = require('child_process');
const msg = 'feat(rma): hide order_status select, always send value 1 via reactive data';
execSync(`git add -A && git commit -m "${msg}"`, {
    cwd: 'C:\\Users\\inkis\\Herd\\goloba-prod\\packages\\Goloba\\GolobaRMA',
    stdio: 'inherit'
});
