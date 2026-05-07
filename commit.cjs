const { execSync } = require('child_process');
const path = 'C:\\Users\\inkis\\Herd\\goloba-prod\\packages\\Goloba\\GolobaRMA';

try {
    execSync('git add -A', { cwd: path, stdio: 'inherit' });
    execSync('git commit -m "feat(rma): seal checkbox in standard modal; restrict seller status block to accepted RMAs"', { cwd: path, stdio: 'inherit' });
    console.log('Commit exitoso');
} catch (e) {
    console.error('Error:', e.message);
}
