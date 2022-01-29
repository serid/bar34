const hideAuthAndShowUploadBlock = () => {
    document.getElementById("auth-block").style.display = "none";
    document.getElementById("alert").style.display = "none";

    document.getElementById("upload-block").style.display = "block";
};

const reverseHideAuthAndShowUploadBlock = () => {
    document.getElementById("auth-block").style.display = "block";
    document.getElementById("alert").style.display = "block";

    document.getElementById("upload-block").style.display = "none";
};

const updateHiddenKey = () => {
    let key = localStorage.getItem("key");
    if (key === null) key = "";
    document.getElementById("inputHiddenKey").value = key;
};

const main = () => {
    // If there is a saved key, skip authentication
    if (localStorage.getItem("key") !== null) {
        hideAuthAndShowUploadBlock();
    }

    updateHiddenKey();
};
window.addEventListener("load", main);

window.my = {
    auth: () => {
        let key = document.getElementById("inputKey").value;

        (async () => {
            let response = await fetch(`handle_files.php?key=${key}`);
            let text = await response.text();
            if (text === "ok") {
                // Save key
                localStorage.setItem("key", key);
                hideAuthAndShowUploadBlock();
                updateHiddenKey();
            } else {
                // Display a message indicating an authentication error

                let newElement = document.createElement("div");
                let newText = document.createTextNode(text === "wrongkey" ? "Код неверный." : "Ошибка сервера.");
                newElement.appendChild(newText);
                newElement.className = "alert-danger my-alert-success";

                let alertElement = document.getElementById("alert");
                alertElement.appendChild(newElement);
            }
        })();
    },

    deauth: () => {
        localStorage.removeItem("key");
        reverseHideAuthAndShowUploadBlock();
        updateHiddenKey();
    }
};

