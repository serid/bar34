import {stat} from "node:fs/promises";

export const type = (obj, fullClass) => {

    // get toPrototypeString() of obj (handles all types)
    // Early JS environments return '[object Object]' for null, so it's best to directly check for it.
    if (fullClass) {
        return (obj === null) ? '[object Null]' : Object.prototype.toString.call(obj);
    }
    if (obj == null) {
        return (obj + '').toLowerCase();
    } // implicit toString() conversion

    let deepType = Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
    if (deepType === 'generatorfunction') {
        return 'function'
    }

    // Prevent overspecificity (for example, [object HTMLDivElement], etc).
    // Account for functionish Regexp (Android <=2.3), functionish <object> element (Chrome <=57, Firefox <=52), etc.
    // String.prototype.match is universally supported.

    return deepType.match(/^(array|bigint|date|error|function|generator|regexp|symbol)$/) ? deepType :
        (typeof obj === 'object' || typeof obj === 'function') ? 'object' : typeof obj;
}

export const testItemExists = async (name) => {
    let yes = true;

    await stat(name).catch((reason) => {
        if (reason.code === "ENOENT") {
            yes = false;
        } else {
            throw reason;
        }
    });

    return yes;
}

export const catchAndPrint = (promise) => {
    return promise.catch((error) => {
        console.error(type(error, true));
        console.error(error);
    });
}
