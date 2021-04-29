import {stat, mkdir} from "node:fs/promises";
import {spawn} from "node:child_process";

import {testItemExists, catchAndPrint} from "./js/lib.mjs";

const maybeCreateDir = async (name) => {
    let nameVacant = false;

    // Build a promise that ingores ENOENT
    await stat(name).then((stats) => {
        if (stats.isDirectory()) {
            console.log(`[] Dir ${name} already exists`);
        } else if (stats.isFile()) {
            console.log(`[] Cannot create dir ${name}. File with this name already exists`);
        } else {
            throw "uh-oh";
        }
    }, (reason) => {
        if (reason.code === "ENOENT") {
            nameVacant = true;
        } else {
            throw reason;
        }
    });

    if (nameVacant)
        await mkdir(name);
}

const aMain = async () => {
    await maybeCreateDir("logs");
    await maybeCreateDir("temp");

    if (await testItemExists("logs\\nginx.pid")) {
        console.log("[] Server is already running");
    } else {
        let subprocess = spawn("C:\\Users\\jitrs\\Documents\\opt\\nginx-1.18.0\\nginx.exe", ["-c", ".\\nginx.conf"], {
            detached: true,
            stdio: "ignore"
        });
        subprocess.unref();
        console.log("[] Server started");
    }
}

catchAndPrint(aMain());