function loadFavorites() {
    fetch("/favorites")
        .then(res => res.json())
        .then(data => {

            const container = document.getElementById("favorites-list");
            container.innerHTML = "";

            if (data.status !== "success") {
                showMessage(data.message, "error");
                return;
            }

            if (data.data.length === 0) {
                container.innerHTML = "<p>No favorites yet</p>";
                return;
            }

            data.data.forEach(fav => {

                const noteText = fav.notes ? `(${fav.notes.slice(0, 25)})` : '';

                const item = document.createElement("div");
                item.className = "fav-item";

                item.innerHTML = `
                    <span class="city-name" data-city="${fav.city_name.toLowerCase()}">
                        ${fav.city_name} ${noteText}
                    </span>
                    <button class="delete-btn" onclick="deleteFavorite(${fav.id})">×</button>
                `;

                item.querySelector(".city-name").addEventListener("click", () => {
                    getWeather(fav.city_name);
                });

                container.appendChild(item);
            });
        })
        .catch(() => {
            showMessage("Failed to load favorites", "error");
        });
}


function saveToFavorites() {

    const city = window.currentCity;
    const note = document.getElementById("notes-input").value;

    if (!city) {
        showMessage("Please search for a city first", "error");
        return;
    }

    const existingCities = document.querySelectorAll(".city-name");

    for (let item of existingCities) {
        const existingCity = item.dataset.city;

        if (existingCity === city.toLowerCase()) {
            showMessage("City already in favorites", "error");
            return;
        }
    }

    fetch("/favorites", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken()
        },
        body: JSON.stringify({
            city_name: city,
            notes: note
        })
    })
    .then(res => res.json())
    .then(data => {
        showMessage(data.message, data.status);
        loadFavorites();
    })
    .catch(() => {
        showMessage("Request failed", "error");
    });
}


function deleteFavorite(id) {
    fetch(`/favorites/${id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": getCsrfToken()
        }
    })
    .then(res => res.json())
    .then(data => {
        showMessage(data.message, data.status);
        loadFavorites();
    })
    .catch(() => {
        showMessage("Delete failed", "error");
    });
}