// The script compiles site in `options.siteRoot` directory into `options.siteRoot`\newbuild.
// Then it moves `options.siteRoot`\newbuild to `options.outputDirectory`.
// Compilation process ignores filepaths containing `options.ignorePattern`;
// Copies filepaths containing `options.copyPattern` without changing file names or contents;
// Copies filepaths     containing `options.textFilePattern` changing file name to a randomly generated string AND changes all references to files with randomized names to correct names;
// Copies filepaths not containing `options.textFilePattern` changing file name to a randomly generated string without changing file contents;

const options = {
    siteRoot: '.\\static\\',
    outputDirectory: '.\\build\\',

    ignorePattern: /\.rese/,
    copyPattern: /(lib\\|.*\.php)/,
    textFilePattern: /(\.html$|\.js$)/,
}

import {dirname, relative, resolve, extname} from "node:path";
import {opendir, copyFile, mkdir, readFile, writeFile, rmdir, rename} from "node:fs/promises";
import {cwd, chdir} from "node:process";

import {catchAndPrint} from "./js/lib.mjs";

const asyncGenToArray = async (ag) => {
    let result = [];
    for await (const x of ag) {
        result.push(x);
    }
    return result;
}

// Yields paths to all the files in a directory tree at `startPath`
const getDirFilesRecursive = async function* (startPath) {
    let pathsToBeVisited = [startPath];

    do {
        let dirPath = pathsToBeVisited.pop();
        let dir = await opendir(dirPath);

        for await (const item of dir) {
            let itemPath = resolve(dirPath, item.name);
            itemPath = relative('', itemPath);
            if (item.isDirectory()) {
                pathsToBeVisited.push(itemPath);
            } else if (item.isFile()) {
                yield itemPath;
            } else {
                throw 'Err';
            }
        }
    } while (pathsToBeVisited.length !== 0)
}

// Needs to be a generator to keep track of strings that were already used and cannot be used again
const genString = function* () {
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    let usedStrings = new Set;

    while (true) {
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }

        if (usedStrings.has(result))
            continue;
        usedStrings.add(result);

        yield result;
    }
}

// This function is suboptimal
const replaceAllOfMap = (s, mapping) => {
    let result = s;
    for (const [key, value] of mapping) {
        result = result.replace(new RegExp(key, 'g'), value);
    }
    return result;
}

const myFsPromises = {
    copyFile: async (src, dest, ...args) => {
        await copyFile(src, dest, ...args).catch((reason) => {
            // If the reason is ENOENT (tried to create a file in nonexistent directory), create the directory and retry.
            if (reason.code === 'ENOENT') {
                let newDirName = dirname(dest);
                console.log(`Creating directory: ${relative('', newDirName)}`);
                mkdir(newDirName, {recursive: true}).then(() => {
                    return copyFile(src, dest, ...args);
                });
            } else {
                throw reason;
            }
        });
    }
}

const dostuff = async () => {
    let filepaths = await asyncGenToArray(getDirFilesRecursive('.'));

    // Filter out files that won't be included in the build
    let isIgnoredFile = (path) => options.ignorePattern.test(path);
    filepaths = filepaths.filter((s) => !isIgnoredFile(s));

    // Copy library files verbatim
    let isLibFile = (path) => options.copyPattern.test(path);
    let libspaths = filepaths.filter((s) => isLibFile(s));
    for (const oldPath of libspaths) {
        let newPath = resolve('newbuild', oldPath);
        await myFsPromises.copyFile(oldPath, newPath);
    }
    // Rest of the files will be renamed and processed
    filepaths = filepaths.filter((s) => !isLibFile(s));

    // Generate new names(paths) for files
    let generator = genString();
    let mapping = new Map();
    let replace_mapping = new Map();
    for (const oldPath of filepaths) {
        // Mapping of old filenames to new filenames
        let newPath = generator.next().value + extname(oldPath);
        mapping.set(oldPath, newPath);

        // In text source code paths use '/' as a delimeter and the replace_mapping should too.
        let pathInSourceCode = oldPath.replace(/\\/g, '/');
        replace_mapping.set(pathInSourceCode, newPath);
    }

    // index.html has to be included in build but should not be renamed
    // so we fix the mapping manually
    mapping.set('index.html', 'index.html');
    replace_mapping.set('index.html', 'index.html');

    // Split files in two groups:
    // 1) Simple (binary) files. They don't have relevant filenames in them.
    // 2) Text files. They might have relevant filenames inside that need to be replaced.

    let isTextFile = (path) => options.textFilePattern.test(path);

    let simplepaths = filepaths.filter((s) => !isTextFile(s));
    let textpaths = filepaths.filter((s) => isTextFile(s));

    // Copy simple files verbatim
    for (const oldPath of simplepaths) {
        let newPath = resolve('newbuild', mapping.get(oldPath));
        await myFsPromises.copyFile(oldPath, newPath);
    }

    // Replace old occurences of paths with new paths in text files
    // TODO: this code can be made concurrent
    for (const oldPath of textpaths) {
        let text = await readFile(oldPath, {encoding: 'utf-8'});
        let newText = replaceAllOfMap(text, replace_mapping);

        let newPath = resolve('newbuild', mapping.get(oldPath));

        await writeFile(newPath, newText);
        console.log(`Writing file: ${relative('', newPath)}`);
    }
    console.log(replace_mapping)
}

const aMain = async () => {
    let startingCWD = resolve(cwd());
    chdir(options.siteRoot);

    await dostuff().finally(() => {
        chdir(startingCWD);
    });

    await rmdir(options.outputDirectory, {recursive: true});
    await rename(resolve(options.siteRoot, '.\\newbuild\\'), options.outputDirectory);
}

catchAndPrint(aMain());