// The script compiles site in `options.siteRoot` directory into `options.siteRoot`\newbuild,
// then it moves `options.siteRoot`\newbuild to `options.outputDirectory`.
// Compilation process ignores filepaths matching `options.ignorePattern`;
// Copies filepaths matching `options.libPattern` without changing file names or contents;
// Copies filepaths matching `options.textFilePattern` changing file name to a randomly generated string AND changes all references to files with randomized names to correct names;
// Copies filepaths not matching `options.textFilePattern` changing file name to a randomly generated string without changing file contents;

const options = {
    siteRoot: '.\\static\\',
    outputDirectory: '.\\build\\',

    ignorePattern: /\.rese/,
    libPattern: /(lib\\)/,
    entryPointPattern: /(\.html|\.php)$/,
    textFilePattern: /(\.html|\.js)$/,
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
    copyFile: async (src, dest, mode = undefined) => {
        await copyFile(src, dest, mode).catch((reason) => {
            // If the reason is ENOENT (tried to create a file in nonexistent directory), create the directory and retry.
            if (reason.code === 'ENOENT') {
                let newDirName = dirname(dest);
                console.log(`Creating directory: ${relative('', newDirName)}`);
                mkdir(newDirName, {recursive: true}).then(() => {
                    return copyFile(src, dest, mode);
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
    let isLibFile = (path) => options.libPattern.test(path);
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
        let newPath;
        // If a file is an entry point, then don't modify its name
        if (options.entryPointPattern.test(oldPath)) {
            newPath = oldPath;
        } else {
            newPath = generator.next().value + extname(oldPath);
        }

        // In text source code paths use '/' as a delimeter and the replace_mapping should too
        let pathInSourceCode = oldPath.replace(/\\/g, '/');

        mapping.set(oldPath, newPath);
        replace_mapping.set(pathInSourceCode, newPath);
    }

    // Don't replace paths inside binary files
    let isTextFile = (path) => options.textFilePattern.test(path);

    let binarypaths = filepaths.filter((s) => !isTextFile(s));
    let textpaths = filepaths.filter((s) => isTextFile(s));

    // Copy binary files verbatim
    for (const oldPath of binarypaths) {
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