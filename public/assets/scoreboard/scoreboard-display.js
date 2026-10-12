/*
 * Marquoir RBC Disonais — écran TV piloté au pavé numérique.
 *
 * L'état complet de la partie tient dans `st` :
 *   innings : séries terminées dans l'ordre chronologique (index pair = joueur qui casse)
 *   run     : série en cours du joueur au billard
 *   ext     : extensions de chrono consommées [joueur 1, joueur 2]
 *   rev     : version, incrémentée à chaque modification
 * Il est gardé dans le navigateur (survit à un rechargement / une coupure Internet)
 * et poussé au serveur dès que possible ; la version la plus récente l'emporte.
 */
(function () {
    'use strict';

    const C = window.SB_CONFIG;
    const S = C.starter;
    const O = S === 1 ? 2 : 1;
    const dist = (p) => C.distances[p];
    const STORE_KEY = 'sb_match_' + C.id + '_' + C.startedAt;

    const $ = (id) => document.getElementById(id);
    const root = $('sbd');
    const panels = { 1: $('sbdPlayer1'), 2: $('sbdPlayer2') };

    let st = loadState();
    let buffer = '';
    let syncedRev = C.server.rev;
    let lastScores = { 1: null, 2: null };

    // ------------------------------------------------------------ état

    function loadState() {
        const srv = C.server;
        let s = {
            rev: srv.rev,
            innings: srv.innings.slice(),
            run: srv.current_run,
            ext: srv.extensions.slice(),
            finished: srv.status === 'finished',
        };
        if (C.spectator) return s;
        try {
            const local = JSON.parse(localStorage.getItem(STORE_KEY) || 'null');
            if (local && local.rev > s.rev) s = local;
        } catch (e) { /* stockage indisponible : on garde l'état serveur */ }
        return s;
    }

    const playerAt = (i) => (i % 2 === 0 ? S : O);
    const current = () => playerAt(st.innings.length);

    function totals(p, withRun) {
        let pts = 0, inn = 0, hr = 0;
        st.innings.forEach(function (v, i) {
            if (playerAt(i) === p) { pts += v; inn++; hr = Math.max(hr, v); }
        });
        if (withRun && !st.finished && current() === p) {
            pts += st.run; inn++; hr = Math.max(hr, st.run);
        }
        return { pts: pts, inn: inn, hr: hr };
    }

    const remaining = () => dist(current()) - totals(current(), false).pts - st.run;

    function inningNumber() {
        const n = st.innings.length;
        return st.finished ? Math.ceil(n / 2) : Math.floor(n / 2) + 1;
    }

    function isEqualizing() {
        return !st.finished && !C.unequal && st.innings.length % 2 === 1 && totals(S, false).pts >= dist(S);
    }

    function commit() {
        st.rev++;
        try { localStorage.setItem(STORE_KEY, JSON.stringify(st)); } catch (e) { /* ignoré */ }
        render();
        sync();
    }

    // ------------------------------------------------------------ actions

    function addPoint() {
        if (st.finished) return;
        if (buffer) { buffer = ''; }
        if (remaining() <= 0) return;
        st.run++;
        restartClock();
        if (remaining() === 0) {
            endInning();
            return;
        }
        commit();
    }

    function endInning() {
        if (st.finished) return;
        if (buffer !== '') {
            st.run = Math.min(parseInt(buffer, 10) || 0, dist(current()) - totals(current(), false).pts);
            buffer = '';
        }
        st.innings.push(st.run);
        st.run = 0;
        clock.expired = false;
        checkEnd();
        if (st.finished) {
            stopClock();
        } else {
            resetClock();
        }
        commit();
    }

    function checkEnd() {
        const n = st.innings.length;
        const s1 = totals(S, false).pts;
        const s2 = totals(O, false).pts;
        if (n % 2 === 1) {
            // Le joueur qui casse vient de jouer : sans égalisation, il gagne immédiatement
            if (s1 >= dist(S) && C.unequal) st.finished = true;
        } else {
            if (s1 >= dist(S) || s2 >= dist(O)) st.finished = true;
            if (C.inningsLimit && n / 2 >= C.inningsLimit) st.finished = true;
        }
    }

    function minus() {
        if (buffer) {
            buffer = buffer.slice(0, -1);
            render();
            return;
        }
        if (st.finished) {
            st.finished = false;
        } else if (st.run > 0) {
            st.run--;
            commit();
            return;
        }
        if (st.innings.length) {
            st.run = st.innings.pop();
            clock.expired = false;
            stopClock();
        }
        commit();
    }

    function digit(d) {
        if (st.finished || buffer.length >= 4) return;
        buffer = (buffer === '0' ? '' : buffer) + d;
        render();
    }

    function clearBuffer() {
        buffer = '';
        render();
    }

    // ------------------------------------------------------------ chrono par coup

    // expired : temps écoulé sans time-out restant, le chrono reste bloqué jusqu'à la fin de reprise (Entrée)
    const clock = { total: C.clock.seconds * 1000, left: C.clock.seconds * 1000, running: false, endAt: 0, lastSec: null, expired: false };

    function clockLeft() {
        return clock.running ? clock.endAt - Date.now() : clock.left;
    }

    function restartClock() {
        if (!C.clock.enabled || st.finished || clock.expired) return;
        clock.total = clock.left = C.clock.seconds * 1000;
        clock.running = true;
        clock.endAt = Date.now() + clock.left;
        clock.lastSec = null;
    }

    function resetClock() {
        clock.total = clock.left = C.clock.seconds * 1000;
        clock.running = false;
        clock.lastSec = null;
    }

    function stopClock() {
        clock.left = Math.max(0, clockLeft());
        clock.running = false;
    }

    function toggleClock() {
        if (!C.clock.enabled || st.finished || clock.expired) return;
        if (clock.running) {
            stopClock();
        } else if (clock.left <= 0) {
            restartClock();
        } else {
            clock.running = true;
            clock.endAt = Date.now() + clock.left;
        }
        renderClock();
    }

    function extension() {
        if (!C.clock.enabled || st.finished) return;
        const i = current() - 1;
        if (st.ext[i] >= C.clock.extensions - 1) return;
        st.ext[i]++;
        const add = C.clock.seconds * 1000;
        if (clock.running) {
            clock.endAt = Math.max(clock.endAt, Date.now()) + add;
        } else {
            clock.left = Math.max(clock.left, 0) + add;
            clock.running = true;
            clock.endAt = Date.now() + clock.left;
        }
        clock.total = clockLeft();
        clock.lastSec = null;
        commit();
    }

    function renderClock() {
        const box = $('sbdClock');
        // Le chrono vit sur le Chromebook du billard : il n'est pas transmis aux spectateurs
        if (!C.clock.enabled || C.spectator) return;
        box.hidden = false;

        if (clock.running && clockLeft() <= 0) {
            // Chaque dépassement consomme un time-out ; celui qui épuise le compteur arrête le chrono
            const i = current() - 1;
            if (st.ext[i] < C.clock.extensions) st.ext[i]++;
            const left = C.clock.extensions - st.ext[i];
            if (left > 0) {
                clock.total = clock.left = C.clock.seconds * 1000;
                clock.endAt = Date.now() + clock.left;
                clock.lastSec = null;
                flash('Time-out utilisé\n(' + left + ' restant' + (left > 1 ? 's' : '') + ')');
                commit();
                return;
            }
            // Dernier time-out : on s'arrête sur « Temps écoulé » ; c'est l'arbitre qui passe la main (Entrée)
            clock.running = false;
            clock.left = 0;
            clock.expired = true;
            flashMsg = '';
            commit();
            return;
        }

        const left = clockLeft();
        const sec = Math.max(0, Math.ceil(left / 1000));
        const bar = $('sbdClockBar');
        $('sbdClockNum').textContent = sec;
        bar.style.width = Math.max(0, Math.min(100, left / clock.total * 100)) + '%';
        bar.classList.toggle('warn', sec <= 15 && sec > 5);
        bar.classList.toggle('danger', sec <= 5);
        box.classList.toggle('paused', !clock.running);
        box.classList.toggle('expired', left <= 0 && !st.finished);

        if (clock.running && sec !== clock.lastSec) {
            // Un seul bip, à 10 s : les bips répétés dérangeaient les joueurs
            if (sec === 10) beep(880, 0.12);
            clock.lastSec = sec;
        }
    }

    let flashMsg = '', flashTimer = null;
    function flash(msg) {
        flashMsg = msg;
        clearTimeout(flashTimer);
        flashTimer = setTimeout(function () { flashMsg = ''; render(); }, 4000);
    }

    let audio = null;
    function beep(freq, duration) {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            const osc = audio.createOscillator();
            const gain = audio.createGain();
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.25, audio.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audio.currentTime + duration);
            osc.connect(gain).connect(audio.destination);
            osc.start();
            osc.stop(audio.currentTime + duration);
        } catch (e) { /* pas de son disponible */ }
    }

    // ------------------------------------------------------------ synchronisation serveur

    let inflight = false, retryTimer = null;

    function setSync(cls) {
        const el = $('sbdSync');
        el.className = 'sbd-sync' + (cls ? ' ' + cls : '');
        el.title = cls === 'offline' ? 'Hors ligne — les points sont gardés sur cet appareil'
                 : cls === 'pending' ? 'Envoi en cours…' : 'Synchronisé';
    }

    function sync() {
        if (C.spectator) return;
        if (st.rev <= syncedRev) { setSync(''); return; }
        if (inflight) return;
        inflight = true;
        clearTimeout(retryTimer);
        setSync('pending');
        const sent = st.rev;
        let failed = false;
        fetch(C.urls.save, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ rev: st.rev, innings: st.innings, current_run: st.run, extensions: st.ext, finished: st.finished }),
        }).then(function (r) {
            if (r.status === 409) { window.location.href = C.urls.table; return; }
            if (!r.ok) throw new Error('HTTP ' + r.status);
            syncedRev = Math.max(syncedRev, sent);
        }).catch(function () {
            failed = true;
            setSync('offline');
            retryTimer = setTimeout(sync, 3000);
        }).finally(function () {
            inflight = false;
            // Des points ont pu être marqués pendant l'envoi
            if (!failed) sync();
        });
    }

    window.addEventListener('online', sync);

    // ------------------------------------------------------------ affichage

    const fmtAvg = (pts, inn) => (inn > 0 ? pts / inn : 0).toFixed(3).replace('.', ',');
    const name = (p) => C.players[p].last + ' ' + C.players[p].first;

    function render() {
        const cur = st.finished ? 0 : current();

        [1, 2].forEach(function (p) {
            // La série en cours n'est ajoutée au score qu'à la validation de la reprise (Entrée)
            const t = totals(p, false);
            const el = panels[p];
            el.classList.toggle('active', p === cur);
            const score = el.querySelector('[data-score]');
            score.textContent = t.pts;
            score.classList.toggle('long', t.pts >= 100);
            if (lastScores[p] !== null && lastScores[p] !== t.pts) {
                score.classList.remove('bump'); void score.offsetWidth; score.classList.add('bump');
            }
            lastScores[p] = t.pts;
            el.querySelector('[data-avg]').textContent = fmtAvg(t.pts, t.inn);
            el.querySelector('[data-hr]').textContent = t.hr;
            renderLast5(p, el.querySelector('[data-last5]'));

            const ext = el.querySelector('[data-ext]');
            if (C.clock.enabled && C.clock.extensions > 0) {
                let html = '';
                for (let i = 0; i < C.clock.extensions; i++) {
                    html += '<i class="fas fa-stopwatch' + (i < st.ext[p - 1] ? ' used' : '') + '"></i>';
                }
                ext.innerHTML = html;
            }
        });

        $('sbdInnings').textContent = inningNumber();
        $('sbdInningsLimit').textContent = C.inningsLimit ? 'sur ' + C.inningsLimit : '';

        const runBox = $('sbdRunBox');
        runBox.style.setProperty('--run-color', cur === 2 ? 'var(--sb-p2)' : 'var(--sb-p1)');
        runBox.classList.toggle('typing', buffer !== '');
        $('sbdRun').textContent = buffer !== '' ? buffer : st.run;
        $('sbdHint').textContent = buffer !== '' ? 'Entrée pour valider la série' : '';

        let notice = flashMsg;
        if (clock.expired && !st.finished) notice = 'Temps écoulé\nEntrée pour passer la main';
        else if (notice) { /* message temporaire prioritaire */ }
        else if (isEqualizing()) notice = "Reprise d'égalisation";
        else if (!st.finished && C.inningsLimit && inningNumber() === C.inningsLimit) notice = 'Dernière reprise';
        $('sbdNotice').textContent = notice;

        renderResult();
        renderClock();
    }

    // 5 dernières reprises terminées du joueur, la plus récente en haut
    function renderLast5(p, box) {
        const mine = [];
        st.innings.forEach(function (v, i) { if (playerAt(i) === p) mine.push({ n: mine.length + 1, pts: v }); });
        const last = mine.slice(-5).reverse();
        let html = '';
        for (let k = 0; k < 5; k++) {
            const r = last[k];
            html += r
                ? '<div' + (k === 0 ? ' class="latest"' : '') + '><small>' + r.n + '</small><b>' + r.pts + '</b></div>'
                : '<div class="empty"><small></small><b>–</b></div>';
        }
        box.innerHTML = html;
    }

    function renderResult() {
        const box = $('sbdResult');
        box.classList.toggle('open', st.finished);
        if (!st.finished) return;

        const t1 = totals(1, false), t2 = totals(2, false);
        // Avec des distances différentes, on compare le pourcentage de la distance réalisé
        const cmp = Math.sign(t1.pts * dist(2) - t2.pts * dist(1));
        $('sbdWinner').textContent = cmp === 0 ? 'Match nul' : 'Victoire de ' + name(cmp > 0 ? 1 : 2);
        $('sbdWinner').style.color = cmp === 0 ? '#fff' : (cmp > 0 ? 'var(--sb-p1)' : 'var(--sb-p2)');

        const rows = $('sbdResultRows');
        rows.innerHTML = '';
        [[1, t1], [2, t2]].forEach(function (pair) {
            const tr = document.createElement('tr');
            [name(pair[0]), pair[1].pts, pair[1].inn, fmtAvg(pair[1].pts, pair[1].inn), pair[1].hr].forEach(function (v) {
                const td = document.createElement('td');
                td.textContent = v;
                tr.appendChild(td);
            });
            tr.style.color = pair[0] === 1 ? 'var(--sb-p1)' : 'var(--sb-p2)';
            rows.appendChild(tr);
        });
    }

    // ------------------------------------------------------------ clavier / pavé numérique

    function actionFor(e) {
        const code = e.code, key = e.key;
        if (code === 'NumpadAdd' || key === '+') return 'plus';
        if (code === 'NumpadSubtract' || key === '-') return 'minus';
        if (code === 'NumpadEnter' || code === 'Enter' || key === 'Enter') return 'enter';
        if (code === 'NumpadMultiply' || key === '*') return 'clock';
        if (code === 'NumpadDecimal' || key === ',' || key === '.') return 'ext';
        if (code === 'NumpadDivide' || key === '/' || key === 'Backspace') return 'clear';
        if (key === 'Escape') return 'menu';
        // event.code reste « NumpadX » même avec le Verr. Num désactivé
        if (/^Numpad[0-9]$/.test(code)) return 'd' + code.slice(-1);
        if (/^[0-9]$/.test(key)) return 'd' + key;
        return null;
    }

    if (!C.spectator) {
        const menu = $('sbdMenu');
        const swapForm = $('sbdSwapForm');
        const openMenu = () => {
            // Inverser n'a de sens qu'avant le premier coup (erreur au choix du joueur qui commence)
            swapForm.hidden = st.finished || st.innings.length > 0 || st.run > 0;
            menu.classList.add('open');
            root.classList.add('show-cursor');
        };
        swapForm.addEventListener('submit', function () {
            try { localStorage.removeItem(STORE_KEY); } catch (e) { /* ignoré */ }
        });
        const closeMenu = () => { menu.classList.remove('open'); };

        document.addEventListener('keydown', function (e) {
            // La boîte de confirmation gère elle-même Entrée / Échap
            if (window.Swal && Swal.isVisible()) return;
            const a = actionFor(e);
            if (!a) return;
            e.preventDefault();

            if (menu.classList.contains('open')) {
                if (a === 'menu' || a === 'enter') closeMenu();
                return;
            }
            if (e.repeat && a !== 'plus' && a !== 'minus') return;
            enterFullscreen();

            if (st.finished) {
                if (a === 'enter') window.location.href = C.urls.table;
                else if (a === 'minus') minus();
                else if (a === 'menu') openMenu();
                return;
            }

            switch (a) {
                case 'plus':  addPoint(); break;
                case 'minus': minus(); break;
                case 'enter': endInning(); break;
                case 'clock': toggleClock(); break;
                case 'ext':   extension(); break;
                case 'clear': clearBuffer(); break;
                case 'menu':  openMenu(); break;
                default:      digit(a.slice(1));
            }
        });

        menu.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action]');
            if (e.target === menu || (btn && btn.dataset.action === 'close')) closeMenu();
            if (btn && btn.dataset.action === 'fullscreen') { enterFullscreen(); closeMenu(); }
            if (btn && btn.dataset.action === 'cancel') confirmCancel();
        });

        function confirmCancel() {
            const form = $('sbdCancelForm');
            if (!window.Swal) { form.submit(); return; }
            Swal.fire({
                title: form.dataset.title,
                text: form.dataset.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirm,
                cancelButtonText: 'Non, continuer la partie',
                reverseButtons: true,
                focusCancel: true,
                background: '#16213d',
                color: '#eef1f7',
                iconColor: '#f59e0b',
                confirmButtonColor: '#84252B',
                cancelButtonColor: '#475569',
                customClass: { popup: 'sbd-swal' },
            }).then(function (r) { if (r.isConfirmed) form.submit(); });
        }

        root.addEventListener('dblclick', openMenu);

        let cursorTimer = null;
        document.addEventListener('mousemove', function () {
            root.classList.add('show-cursor');
            clearTimeout(cursorTimer);
            cursorTimer = setTimeout(function () { if (!menu.classList.contains('open')) root.classList.remove('show-cursor'); }, 3000);
        });
    }

    function enterFullscreen() {
        if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen().catch(function () { /* refusé : on reste en fenêtre */ });
        }
    }

    // Empêche le Chromebook de se mettre en veille pendant la partie
    let wakeLock = null;
    async function keepAwake() {
        try {
            if ('wakeLock' in navigator && document.visibilityState === 'visible') {
                wakeLock = await navigator.wakeLock.request('screen');
            }
        } catch (e) { wakeLock = null; }
    }
    document.addEventListener('visibilitychange', keepAwake);
    keepAwake();

    function tickTime() {
        const d = new Date();
        $('sbdTime').textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }
    setInterval(tickTime, 10000);
    tickTime();

    setInterval(function () { if (C.clock.enabled) renderClock(); }, 200);

    // Spectateur : on relit l'état du serveur toutes les 2 secondes
    function poll() {
        fetch(C.urls.state, { cache: 'no-store' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                $('sbdLive').classList.toggle('offline', !d);
                if (!d) return;
                if (d.status === 'scheduled' || d.status === 'cancelled') { window.location.reload(); return; }
                if (d.rev !== st.rev || (d.status === 'finished') !== st.finished) {
                    st = { rev: d.rev, innings: d.innings.slice(), run: d.current_run, ext: d.extensions.slice(), finished: d.status === 'finished' };
                    render();
                }
            })
            .catch(function () { $('sbdLive').classList.add('offline'); });
    }

    render();
    if (C.spectator) {
        setInterval(poll, 2000);
    } else {
        sync();
    }
})();
