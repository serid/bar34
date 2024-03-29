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
    const debounce = identity;

    my.updateIsNavTransparent();

    document.addEventListener('scroll', debounce(my.updateIsNavTransparent), {passive: true});

    window.onload = () => {
        setTimeout(() => {
            document.documentElement.dataset.o2secondspassed = (1).toString();
        }, 0.2);
    }
}

const countAVisit = () => {
    fetch("increment.php?m=inc").then();
}

const getVisitorCount = () => {
    return fetch("increment.php?m=show")
        .then(response => response.text())
        .then(s => s.slice(4));
}

const updateBookingTodayLabel = () => {
    let date = new Date();
    const options = {month: 'long', day: 'numeric'};

    document.getElementById("booking-today-label").innerText = "Сегодня, " + date.toLocaleDateString("ru-RU", options) + ".";
}

// Object for public functions in global scope to call from html events
const my = {
    bookingAlertAdded: false,
    num: 1,

    setNumberOfPeople(n, text) {
        this.num = n;

        document.getElementById("dropdownMenuButton").innerText = text;
    },

    scrollToElement: (element) => {
        if (window.scrollY !== 0) {
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

    updateIsNavTransparent: () => {
        if (document.documentElement.dataset.collapsed === 'true' && window.scrollY === 0) {
            document.documentElement.dataset.isnavtransparent = 'true';
            document.getElementById("instagram-icon").src = "imgs/inst_inverted.png";
        } else {
            document.documentElement.dataset.isnavtransparent = 'false';
            document.getElementById("instagram-icon").src = "imgs/inst.png";
        }
    },

    showVisitorCounter: () => {
        getVisitorCount().then(n => {
            let counter_e = document.getElementById('hidden-counter');
            counter_e.style = "height: 20px;";
            counter_e.textContent = "Посещений сайта за всё время: " + n;
        });
    },

    book() {
        let getValue = (id) => document.getElementById(id).value;

        // input type="time"
        let time = getValue("inputTime");

        // number of people, select tag
        let numberOfPeople = this.num;

        // textarea
        let name = getValue("inputName");

        // input type="tel"
        let phone = getValue("inputPhone");

        // textarea
        let user_message = getValue("inputMessage");

        let body = {time, name, phone, user_message, numberOfPeople};

        let url = "booking.php";
        let options = {
            method: "POST",
            body: JSON.stringify(body),
        };
        fetch(url, options).then(() => {
            if (this.bookingAlertAdded) return;

            let newElement = document.createElement("div");
            let newText = document.createTextNode("Столик успешно забронирован. Ждём вас!");
            newElement.appendChild(newText);
            newElement.className = "alert-success my-alert-success";

            let alertElement = document.getElementById("alert");
            alertElement.appendChild(newElement)

            this.bookingAlertAdded = true;
        });
    }
}

window.my = my;

const main = () => {
    saveScroll();
    countAVisit();
    updateBookingTodayLabel();
}

main();