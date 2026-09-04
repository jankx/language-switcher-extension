#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const BUILD_DIR = path.resolve(__dirname, 'assets/blocks/language-switcher/build');

const renames = [
    ['style-style.css', 'style.css'],
    ['style-style-rtl.css', 'style-rtl.css'],
];

for (const [from, to] of renames) {
    const fromPath = path.join(BUILD_DIR, from);
    const toPath = path.join(BUILD_DIR, to);
    if (fs.existsSync(fromPath)) {
        fs.renameSync(fromPath, toPath);
        console.log(`renamed ${from} -> ${to}`);
    } else {
        console.log(`skip ${from} (not present)`);
    }
}
