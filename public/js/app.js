function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function showSpinner() {
    document.getElementById('spinner').style.display = 'block';
}

function hideSpinner() {
    document.getElementById('spinner').style.display = 'none';
}

function showMessage(text, type) {
    var box = document.getElementById('msg-box');
    box.textContent = text;
    box.className = 'msg ' + type;
    box.style.display = 'block';

    setTimeout(function () {
        box.style.display = 'none';
    }, 3000);
}


function toggleFavorite(btn) {
    const path = btn.querySelector('path');

    if (!path) return;

    const isFaved = path.getAttribute('fill') === 'red';

    if (!window.currentCity) {
        showMessage("Please search a city first", "error");
        return;
    }

    if (isFaved) {
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', 'white');
    } else {
        path.setAttribute('fill', 'red');
        path.setAttribute('stroke', 'red');

        saveToFavorites();
    }
}


function bindSaveButton() {
    const saveBtn = document.querySelector('.save-btn');

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            saveToFavorites();
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {

    loadFavorites();

    // bind heart button (IMPORTANT FIX)
    const favBtn = document.getElementById("fav-btn");
    if (favBtn) {
        favBtn.addEventListener("click", function () {
            toggleFavorite(this);
        });
    }

    // bind save button
    bindSaveButton();
});