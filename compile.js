
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

const path = require('path');
const fs = require('fs');
const process = require('process');

const type = (obj, fullClass) => {

    // get toPrototypeString() of obj (handles all types)
    // Early JS environments return '[object Object]' for null, so it's best to directly check for it.
    if (fullClass) {
        return (obj === null) ? '[object Null]' : Object.prototype.toString.call(obj);
    }
    if (obj == null) { return (obj + '').toLowerCase(); } // implicit toString() conversion

    var deepType = Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
    if (deepType === 'generatorfunction') { return 'function' }

    // Prevent overspecificity (for example, [object HTMLDivElement], etc).
    // Account for functionish Regexp (Android <=2.3), functionish <object> element (Chrome <=57, Firefox <=52), etc.
    // String.prototype.match is universally supported.

    return deepType.match(/^(array|bigint|date|error|function|generator|regexp|symbol)$/) ? deepType :
        (typeof obj === 'object' || typeof obj === 'function') ? 'object' : typeof obj;
}

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
        let dir = await fs.promises.opendir(dirPath);

        for await (const item of dir) {
            let itemPath = path.resolve(dirPath, item.name);
            itemPath = path.relative('', itemPath);
            if (item.isDirectory()) {
                pathsToBeVisited.push(itemPath);
            } else if (item.isFile()) {
                yield itemPath;
            } else {
                throw 'Err';
            }
        }
    } while (pathsToBeVisited.length != 0)
}

const genString = () => {
    let result = '';
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (let i = 0; i < 8; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
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
        let prom = fs.promises.copyFile(src, dest, ...args);
        prom = prom.catch((reason) => {
            // If the reason is ENOENT (tried to create a file in nonexistent directory), create the directory and retry.
            if (reason.code == 'ENOENT') {
                let newDirName = path.dirname(dest);
                console.log(`Creating directory: ${path.relative('', newDirName)}`);
                fs.promises.mkdir(newDirName, { recursive: true }).then(() => {
                    return fs.promises.copyFile(src, dest, ...args);
                });
            } else {
                throw reason;
            }
        })
        await prom;
    }
}

const dostuff = async () => {
    let filepaths = await asyncGenToArray(getDirFilesRecursive('.'));

    // Filter out files that won't be included in the build
    const isIgnoredFile = (path) => options.ignorePattern.test(path);
    filepaths = filepaths.filter((s) => !isIgnoredFile(s));

    // Copy library files verbatim
    const isLibFile = (path) => options.copyPattern.test(path);
    let libspaths = filepaths.filter((s) => isLibFile(s));
    for (const oldPath of libspaths) {
        let newPath = path.resolve('newbuild', oldPath);
        myFsPromises.copyFile(oldPath, newPath);
    }
    // Rest of the files will be renamed and processed
    filepaths = filepaths.filter((s) => !isLibFile(s));

    // Generate new names(paths) for files
    let mapping = new Map();
    let replace_mapping = new Map();
    for (const oldPath of filepaths) {
        // Mapping of old filenames to new filenames
        let newPath = genString() + path.extname(oldPath);
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

    const isTextFile = (path) => options.textFilePattern.test(path);

    let simplepaths = filepaths.filter((s) => !isTextFile(s));
    let textpaths = filepaths.filter((s) => isTextFile(s));

    // Copy simple files verbatim
    for (const oldPath of simplepaths) {
        let newPath = path.resolve('newbuild', mapping.get(oldPath));
        await myFsPromises.copyFile(oldPath, newPath);
    }

    // Replace old occurences of paths with new paths in text files
    for (const oldPath of textpaths) {
        let text = await fs.promises.readFile(oldPath, { encoding: 'utf-8' });
        let newText = replaceAllOfMap(text, replace_mapping);

        let newPath = path.resolve('newbuild', mapping.get(oldPath));

        await fs.promises.writeFile(newPath, newText);
        console.log(`Writing file: ${path.relative('', newPath)}`);
    }
    console.log(replace_mapping)
}

const aMain = async () => {
    let startingCWD = path.resolve(process.cwd());
    process.chdir(options.siteRoot);

    let aMainPromise = dostuff();

    await aMainPromise.finally(() => {
        process.chdir(startingCWD);
    });

    await fs.promises.rmdir(options.outputDirectory, { recursive: true });
    await fs.promises.rename(path.resolve(options.siteRoot, '.\\newbuild\\'), options.outputDirectory);
}

const main = () => {
    let aMainPromise = aMain();

    aMainPromise.catch((error) => {
        console.error(type(error, true))
        console.error(error);
    });
}

main();
