const identity = (a) => a

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
        my.update_isnavtransparent();
    }

    storeScroll()

    document.addEventListener('scroll', debounce(storeScroll), { passive: true });

    window.onload = () => {
        setTimeout(() => {
            document.documentElement.dataset.o2secondspassed = 1;
        }, 0.2);
    }
}

const count_a_visit = () => {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "increment.php?m=inc", true);
    xhr.send();
}

const get_visitor_counter = (f) => {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "increment.php?m=show", true);
    xhr.onload = () => {
        let sliced = xhr.response.slice(4);
        f(sliced);
    }
    xhr.send();
}

// Object for public functions in global scope to call from html events
const my = {
    scrollToElement: (element) => {
        if (window.scrollY != 0) {
            let navbar_height = document.getElementById("my-navbar").getBoundingClientRect().height;
            window.scroll(0, window.scrollY + element.getBoundingClientRect().top - navbar_height + 1);
        } else {
            let logo_e = document.getElementById('logo');
            let html_e = document.getElementsByTagName('html')[0];

            logo_e.style.transitionDuration = "0.0s";

            html_e.style.scrollBehavior = 'auto';
            window.scroll(0, 1);
            html_e.style.scrollBehavior = 'smooth';

            requestAnimationFrame(() => {
                let navbar_height = document.getElementById("my-navbar").getBoundingClientRect().height;
                window.scroll(0, window.scrollY + element.getBoundingClientRect().top - navbar_height + 1);

                logo_e.style.transitionDuration = "";
            })
        }
    },

    update_isnavtransparent: () => {
        if (document.documentElement.dataset.collapsed == 'true' && document.documentElement.dataset.scroll == 0) {
            document.documentElement.dataset.isnavtransparent = 'true';
            document.getElementById("instagram-icon").src = "imgs/inst_inverted.png";
        } else {
            document.documentElement.dataset.isnavtransparent = 'false';
            document.getElementById("instagram-icon").src = "imgs/inst.png";
        }
    },

    show_visitor_counter: () => {
        get_visitor_counter((n) => {
            let counter_e = document.getElementById('hidden-counter');
            counter_e.style = "height: 20px;";
            counter_e.textContent = "Посещений сайта за всё время: " + n;
        })
    },
}

window.my = my;

const main = () => {
    saveScroll();
    count_a_visit();
}

main()