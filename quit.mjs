import {spawnSync} from "node:child_process";

import {testItemExists, catchAndPrint} from "./js/lib.mjs";

const aMain = async () => {
    if (await testItemExists("logs\\nginx.pid")) {
        spawnSync("C:\\Users\\jitrs\\Documents\\opt\\nginx-1.18.0\\nginx.exe", ["-c", ".\\nginx.conf", "-s", "quit"]);
        console.log("[] Server stopped");
    } else {
        console.log("[] Server is not running");
    }
}

catchAndPrint(aMain());