const fs = require('fs');
const path = require('path');
const glob = require('glob');
const globParent = require('glob-parent');
require('colors');

const target = "mybb";
// Paths to copy and watch
const paths = [
    "src/",
    "packages/OUGC-Awards/Upload/",
    "packages/newpoints/Upload/"
].map(path => path + "**/*");

const options = {};
['clean'].forEach(key => {
    const index = process.argv.indexOf(`--${key}`);
    if (index >= 0) {
      options[key] = true;
      process.argv.splice(index, 1);
    }
});

const parents = [...new Set(paths.map(globParent))];
const findTarget = from => {
    const parent = parents
        .filter(p => from.indexOf(p) >= 0)
        .sort()
        .reverse()[0];
    return path.join(target, path.relative(parent, from));
};
const remove = from => {
    const to = findTarget(from);
    const stats = fs.statSync(from);
    if (stats.isDirectory()) {
        return;
    }
    fs.stat(to, (err, stats) => {
        if (null !== err) {
            if (err.code !== 'ENOENT') {
                console.error(err);
                process.exit(1);
            } else {
                // Already deleted
                console.log('[DELETE]'.gray, to);
            }
        } else {
            // Delete file
            fs.unlinkSync(to);
            console.log('[DELETE]'.yellow, to);
        }
    });
};

if (options.clean) {
    paths.forEach(s => glob.sync(s).forEach(remove));
} else {
    process.argv.push(...paths, target);

    require('copy-and-watch');
}