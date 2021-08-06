import {unlink} from "node:fs/promises";

import {catchAndPrint, testItemExists} from "./js/lib.mjs";

const aMain = async () => {
    if (await testItemExists("logs\\nginx.pid")) {
        await unlink("logs\\nginx.pid");
        console.log("[] pid file removed");
    } else {
        console.log("[] pid file not present");
    }
}

catchAndPrint(aMain());