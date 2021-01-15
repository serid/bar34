const identity = (a) => a

const saveScroll = () => {
    // don't debounce
    const debounce = identity

    const storeScroll = () => {
        document.documentElement.dataset.scroll = window.scrollY;
    }

    storeScroll()

    document.addEventListener('scroll', debounce(storeScroll), { passive: true });

    window.onload = () => {
        setTimeout(() => {
            document.documentElement.dataset.o2secondspassed = 1;
        }, 0.2);
    }
}

const main = () => {
    saveScroll()
    console.log('a')
}

main()