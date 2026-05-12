// =============================================
// This is the COMPLETE replacement for API_Ops.js
// Drop it in as-is — only renderForecast() changed
// =============================================

// store current city globally for saving to favorites
window.currentCity = null;
window.currentCountry = null;


function searchWeather() {
    var city = document.getElementById('city-input').value.trim() || 'Cairo';

    if (city === '') {
        showMessage('Please enter a city name.', 'error');
        return;
    }

    var regex = /^[a-zA-ZÀ-ÿ\s\-'.]+$/u;
    if (!regex.test(city)) {
        showMessage('City name must contain only letters.', 'error');
        return;
    }

    showSpinner();

    fetch('/weather', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ city: city })
    })
    .then(res => res.json())
    .then(data => {
        hideSpinner();

        if (data.error) {
            showMessage(data.error, 'error');
            return;
        }

        displayWeather(data);
    })
    .catch(() => {
        hideSpinner();
        showMessage('Something went wrong. Try again.', 'error');
    });

    const path = document.querySelector('#fav-btn path');
    path.setAttribute('fill', 'none');
    path.setAttribute('stroke', 'white');
}

function getWeather(city) {
    document.getElementById('city-input').value = city;
    searchWeather();
}

function displayWeather(data) {
    document.getElementById('city-name').textContent = data.location_name + ', ' + data.country;
    document.getElementById('temp').textContent = data.temp_c;
    document.getElementById('condition').textContent = data.condition_text;
    document.getElementById('humidity').textContent = data.humidity + '%';
    document.getElementById('wind-speed').textContent = data.wind_kph + ' km/h';
    document.getElementById('cloudy').textContent = data.cloud + '%';
    document.getElementById('pressure').textContent = data.pressure_mb + ' mb';
    document.getElementById('date').textContent = new Date().toLocaleDateString('en-US', {
        month: 'long', day: 'numeric', year: 'numeric'
    });

    // weather icon
    var weatherIcon = document.getElementById('weather-icon');
    weatherIcon.innerHTML = '<img src="https:' + data.icon + '" alt="' + data.condition_text + '" style="width:48px;" />';

    // background image based on condition
    var weatherBg = document.querySelector('.bg-wrapper');
    var condition = data.condition_text.toLowerCase();
    const isNight = data.icon.includes('/night/');

    if (condition.includes('rain'))         weatherBg.style.backgroundImage = "url('./assets/img/rainy.jpg')";
    else if (condition.includes('cloud'))   weatherBg.style.backgroundImage = "url('./assets/img/cloudy.jpg')";
    else if (condition.includes('snow'))    weatherBg.style.backgroundImage = "url('./assets/img/snowy.jpg')";
    else if (condition.includes('thunder')) weatherBg.style.backgroundImage = "url('./assets/img/thunder.jpg')";
    else if (condition.includes('storm'))   weatherBg.style.backgroundImage = "url('./assets/img/storm.jpg')";
    else if (condition.includes('mist'))    weatherBg.style.backgroundImage = "url('./assets/img/mist.jpg')";
    else if (condition.includes('sunny'))   weatherBg.style.backgroundImage = "url('./assets/img/sunny.jpg')";
    else if (condition.includes('overcast'))weatherBg.style.backgroundImage = "url('./assets/img/overcast.jpg')";
    else {
        if (isNight) {
            weatherBg.style.backgroundImage = "url('./assets/img/clear.jpg')";
        } else {
            weatherBg.style.backgroundImage = "url('./assets/img/clear_morning.jpg')";
        }
    }

    // save current city globally for favorites
    window.currentCity = data.location_name;
    window.currentCountry = data.country;
    document.getElementById('city-hidden').value = data.location_name;

    renderForecast(data.forecast);
}

// =============================================
// renderForecast — IMAGE-STYLE with SVG wave
// =============================================
function renderForecast(forecastDays) {
    const container = document.getElementById('forecast-days');
    if (!container) return;

    container.innerHTML = '';

    // Ensure exactly 5 days
    forecastDays = forecastDays.slice(0, 5);

    const todayStr = new Date().toISOString().split('T')[0];
    const todayIndex = forecastDays.findIndex(d => d.date === todayStr);
    // fallback: mark middle day as active if today not in range
    const activeIndex = todayIndex !== -1 ? todayIndex : Math.floor(forecastDays.length / 2);

    // ── 1. Wrap everything in a relative wrapper ──────────────────────────
    const wrapper = document.createElement('div');
    wrapper.className = 'forecast-wrapper';

    // ── 2. TOP ROW: icon + temp for each day ─────────────────────────────
    const row = document.createElement('div');
    row.className = 'forecast-row';
    row.id = 'forecast-top-row';

    forecastDays.forEach(function(day, i) {
        const isActive = i === activeIndex;
        const iconUrl  = day.icon ? 'https:' + day.icon.replace(/^https?:/, '') : '';

        const item = document.createElement('div');
        item.className = 'forecast-item' + (isActive ? ' active' : '');

        const icon = document.createElement('img');
        icon.src   = iconUrl;
        icon.alt   = 'icon';

        const temp = document.createElement('p');
        temp.className   = 'f-temp';
        temp.textContent = day.avgtemp_c + '°';

        item.appendChild(icon);
        item.appendChild(temp);
        row.appendChild(item);
    });

    wrapper.appendChild(row);

    // ── 3. SVG WAVE connecting the temperature dots ───────────────────────
    //    Heights are mapped from temperatures so the curve rises/falls naturally.
    const SVG_W  = 1000;   // internal SVG coordinate width
    const SVG_H  = 80;     // internal SVG coordinate height
    const PAD_X  = (SVG_W / forecastDays.length) / 2; // half-cell padding so dots align with items

    const temps = forecastDays.map(d => d.avgtemp_c);
    const minT  = Math.min(...temps);
    const maxT  = Math.max(...temps);
    const range = maxT - minT || 1;

    // Map temp → y (higher temp = lower y = higher on SVG)
    function tempToY(t) {
        const norm = (t - minT) / range;          // 0..1
        return SVG_H - 12 - norm * (SVG_H - 28);  // invert: hot = top
    }

    const step = (SVG_W - PAD_X * 2) / (forecastDays.length - 1);

    // Build point coordinates
    const points = forecastDays.map((day, i) => ({
        x: PAD_X + i * step,
        y: tempToY(day.avgtemp_c)
    }));

    // Create smooth cubic bezier path through all points
    function smoothPath(pts) {
        if (pts.length < 2) return '';
        let d = `M ${pts[0].x} ${pts[0].y}`;
        for (let i = 0; i < pts.length - 1; i++) {
            const cp1x = pts[i].x + (pts[i + 1].x - pts[i].x) / 3;
            const cp1y = pts[i].y;
            const cp2x = pts[i].x + 2 * (pts[i + 1].x - pts[i].x) / 3;
            const cp2y = pts[i + 1].y;
            d += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${pts[i+1].x} ${pts[i+1].y}`;
        }
        return d;
    }

    const pathD = smoothPath(points);

    // Filled area path (close to bottom)
    const areaD = pathD
        + ` L ${points[points.length-1].x} ${SVG_H}`
        + ` L ${points[0].x} ${SVG_H} Z`;

    const svgNS = 'http://www.w3.org/2000/svg';
    const svg   = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', `0 0 ${SVG_W} ${SVG_H}`);
    svg.setAttribute('preserveAspectRatio', 'none');
    svg.classList.add('forecast-wave');

    // Gradient fill under curve
    const defs = document.createElementNS(svgNS, 'defs');
    const grad = document.createElementNS(svgNS, 'linearGradient');
    grad.setAttribute('id', 'waveGrad');
    grad.setAttribute('x1', '0'); grad.setAttribute('y1', '0');
    grad.setAttribute('x2', '0'); grad.setAttribute('y2', '1');

    const stop1 = document.createElementNS(svgNS, 'stop');
    stop1.setAttribute('offset', '0%');
    stop1.setAttribute('stop-color', 'rgba(255,255,255,0.18)');

    const stop2 = document.createElementNS(svgNS, 'stop');
    stop2.setAttribute('offset', '100%');
    stop2.setAttribute('stop-color', 'rgba(255,255,255,0.00)');

    grad.appendChild(stop1);
    grad.appendChild(stop2);
    defs.appendChild(grad);
    svg.appendChild(defs);

    // Area fill
    const area = document.createElementNS(svgNS, 'path');
    area.setAttribute('d', areaD);
    area.setAttribute('fill', 'url(#waveGrad)');
    svg.appendChild(area);

    // Stroke line
    const line = document.createElementNS(svgNS, 'path');
    line.setAttribute('d', pathD);
    line.setAttribute('fill', 'none');
    line.setAttribute('stroke', 'rgba(255,255,255,0.55)');
    line.setAttribute('stroke-width', '2.5');
    line.setAttribute('stroke-linecap', 'round');
    svg.appendChild(line);

    // Dots on each point — active dot is larger + glowing
    points.forEach((pt, i) => {
        const isActive = i === activeIndex;

        if (isActive) {
            // glow ring
            const ring = document.createElementNS(svgNS, 'circle');
            ring.setAttribute('cx', pt.x);
            ring.setAttribute('cy', pt.y);
            ring.setAttribute('r', '10');
            ring.setAttribute('fill', 'rgba(255,255,255,0.2)');
            svg.appendChild(ring);
        }

        const dot = document.createElementNS(svgNS, 'circle');
        dot.setAttribute('cx', pt.x);
        dot.setAttribute('cy', pt.y);
        dot.setAttribute('r', isActive ? '6' : '3.5');
        dot.setAttribute('fill', '#ffffff');
        if (isActive) dot.setAttribute('filter', 'drop-shadow(0 0 5px rgba(255,255,255,0.9))');
        svg.appendChild(dot);
    });

    wrapper.appendChild(svg);

    // ── 4. BOTTOM ROW: day names ──────────────────────────────────────────
    const dayRow = document.createElement('div');
    dayRow.className = 'forecast-row';
    dayRow.style.marginTop = '0';

    forecastDays.forEach(function(day, i) {
        const isActive = i === activeIndex;

        const item = document.createElement('div');
        item.className = 'forecast-item' + (isActive ? ' active' : '');

        const dayName = document.createElement('p');
        dayName.className   = 'f-day';
        dayName.textContent = new Date(day.date).toLocaleDateString('en-US', { weekday: 'long' });

        item.appendChild(dayName);
        dayRow.appendChild(item);
    });

    wrapper.appendChild(dayRow);

    // ── 5. Replace container content ─────────────────────────────────────
    container.appendChild(wrapper);
}

// load default city on page load — searches Cairo silently without showing it in the input
window.addEventListener('load', function() {
    document.getElementById('city-input').value = 'Cairo';
    searchWeather();
    document.getElementById('city-input').value = '';
});

// Allow searching by pressing Enter in the city input
document.getElementById('city-input').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        searchWeather();
    }
});