const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const projectRoot = path.resolve(__dirname, '..');
const pkg = require(path.join(projectRoot, 'package.json'));
const version = pkg.version || '0.0.0';
const outputName = `beer-list-${version}.zip`;

const inclusions = ['beer-list.php', 'includes', 'build', 'README.md', 'LICENSE'].filter((item) =>
	fs.existsSync(path.join(projectRoot, item))
);

if (!inclusions.length) {
	console.error('Nothing to package: expected runtime files were not found.');
	process.exit(1);
}

const exclusions = [
	'node_modules/*',
	'src/*',
	'.git/*',
	'.gitignore',
	'package-lock.json',
	'package.json',
	'webpack.config.js',
	'scripts/*',
];

const zipArgs = [
	'-r',
	outputName,
	...inclusions,
	'-x',
	...exclusions.map((pattern) => `"${pattern}"`),
].join(' ');

try {
	console.log(`Packaging plugin to ${outputName}...`);
	execSync(`cd "${projectRoot}" && zip ${zipArgs}`, { stdio: 'inherit' });
	console.log('Package created successfully.');
} catch (error) {
	console.error('Packaging failed:', error.message);
	process.exit(error.status || 1);
}
