import {spawnSync} from "node:child_process";
import {readdir, readFile, writeFile} from "node:fs/promises";

import {catchAndPrint} from "./js/lib.mjs";

const aMain = async () => {
    let counter = await readFile("counter.txt");
    counter = parseInt(counter.toString()) + 1;
    await writeFile("counter.txt", counter.toString());

    let host = "34.spb.ru";
    let siteName = "34.spb.ru";

    let reserveCopyNumber = counter;
    let reserveCopyName = `reservecopy.${reserveCopyNumber}`;

    // List of filenames to be sent to server (no lib files)
    let buildDir = "build";
    let buildFileNames = await readdir(buildDir);
    buildFileNames = buildFileNames.filter(name => name !== "lib"); // Skip lib directory
    let buildFilePaths = buildFileNames.map(name => buildDir + "/" + name);
    let sendCommands = buildFilePaths.map(c => `send ${c}\n`).join('');

    let ftpCommands = `u1242320
haskell1970
cd www
ls
rename ${siteName} ${reserveCopyName}
mkdir ${siteName}
cd ${siteName}
${sendCommands}
`;

    await writeFile("ftp.txt", ftpCommands);

    let subprocess = spawnSync("ftp", ["-s:ftp.txt", host]);

    console.log(subprocess.stdout.toString());
}

catchAndPrint(aMain());