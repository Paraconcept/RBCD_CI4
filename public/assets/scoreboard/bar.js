(function () {
    'use strict';

    const C = window.BAR_CONFIG;
    const grid = document.getElementById('barGrid');
    const offline = document.getElementById('barOffline');
    const unlock = document.getElementById('barUnlock');

    let tables = [];
    let fetchedAt = 0;
    const beeped = {}; // id de l'appel → instant (ms) du dernier bip
    const served = {}; // servis sur cette tablette : une réponse partie avant le clic ne doit pas les rallumer
    let audio = null;

    // Les navigateurs n'autorisent le son qu'après un premier toucher de l'écran
    unlock.addEventListener('click', function () {
        try { audio = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) { audio = null; }
        unlock.hidden = true;
        if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(function () {});
    });

    function beep(times) {
        if (!audio) return;
        for (let i = 0; i < times; i++) {
            const t = audio.currentTime + i * 0.35;
            const osc = audio.createOscillator(), gain = audio.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.6, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.25);
            osc.connect(gain).connect(audio.destination);
            osc.start(t);
            osc.stop(t + 0.3);
        }
    }

    function fmtWait(s) {
        if (s < 60) return "à l'instant";
        const m = Math.floor(s / 60);
        return 'depuis ' + m + ' min';
    }

    function waiting(call) {
        return call.waiting + Math.floor((Date.now() - fetchedAt) / 1000);
    }

    function render() {
        grid.innerHTML = '';
        tables.forEach(function (t) {
            const tile = document.createElement('div');
            tile.className = 'bar-tile';
            const name = document.createElement('div');
            name.className = 'bar-tile-name';
            name.textContent = t.name;
            const players = document.createElement('div');
            players.className = 'bar-tile-players';
            players.textContent = t.players ? t.players.join(' – ') : '';
            const status = document.createElement('div');
            status.className = 'bar-tile-status';

            if (t.call) {
                const w = waiting(t.call);
                tile.classList.add('called');
                tile.classList.toggle('late', w >= C.orangeAfter && w < C.redAfter);
                tile.classList.toggle('very-late', w >= C.redAfter);
                status.innerHTML = '<i class="fas fa-bell me-2"></i>';
                status.appendChild(document.createTextNode('Appel ' + fmtWait(w)));
                tile.addEventListener('click', function () { serve(t); });
            }
            tile.append(name, players, status);
            grid.appendChild(tile);
        });
    }

    // Trois bips à l'arrivée d'un appel, puis un rappel régulier tant qu'il n'est pas servi
    function ring() {
        const now = Date.now();
        let times = 0;
        tables.forEach(function (t) {
            if (!t.call) return;
            const last = beeped[t.call.id];
            if (last === undefined) { beeped[t.call.id] = now; times = 3; }
            else if (now - last >= C.beepEvery * 1000) { beeped[t.call.id] = now; times = Math.max(times, 1); }
        });
        if (times) beep(times);
    }

    function serve(t) {
        const id = t.call.id;
        served[id] = true;
        t.call = null;
        render();
        fetch(C.serve + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .catch(function () { /* rattrapé par l'interrogation suivante */ })
            .then(poll);
    }

    function poll() {
        fetch(C.state, { cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                offline.hidden = true;
                tables = d.tables;
                tables.forEach(function (t) { if (t.call && served[t.call.id]) t.call = null; });
                fetchedAt = Date.now();
                render();
                ring();
            })
            .catch(function () { offline.hidden = false; });
    }

    poll();
    setInterval(poll, 2000);
})();
