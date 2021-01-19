const identity = (a) => a

const log = (f) => (...params) => {
    console.log('a')
    f(...params)
}

const update_isnavtransparent = () => {
    if (document.documentElement.dataset.collapsed == 'true' && document.documentElement.dataset.scroll == 0) {
        document.documentElement.dataset.isnavtransparent = 'true';
    } else {
        document.documentElement.dataset.isnavtransparent = 'false';
    }
}

const saveScroll = () => {
    // const debounce = (fn) => {
    //     // This holds the requestAnimationFrame reference, so we can cancel it if we wish
    //     let frame;
    //
    //     // The debounce function returns a new function that can receive a variable number of arguments
    //     return (...params) => {
    //         // If the frame variable has been defined, clear it now, and queue for next frame
    //         if (frame) {
    //             cancelAnimationFrame(frame);
    //         }

    //         // Queue our function call for the next frame
    //         frame = requestAnimationFrame(() => {

    //             // Call our function and pass any params we received
    //             fn(...params);
    //         });
    //     }
    // };

    // don't debounce
    const debounce = identity

    const storeScroll = () => {
        document.documentElement.dataset.scroll = window.scrollY;
        update_isnavtransparent();
    }

    storeScroll()

    document.addEventListener('scroll', debounce(log(storeScroll)), { passive: true });

    window.onload = () => {
        setTimeout(() => {
            document.documentElement.dataset.o2secondspassed = 1;
        }, 0.2);
    }
}

const main = () => {
    saveScroll()
}

main()