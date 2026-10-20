/*
 * Marquoir RBC Disonais — écran TV piloté au pavé numérique.
 *
 * L'état complet de la partie tient dans `st` :
 *   innings : séries terminées dans l'ordre chronologique (joueurs en alternance, découpées en sets par walk())
 *   run     : série en cours du joueur au billard
 *   ext     : extensions de chrono consommées [joueur 1, joueur 2] (par sets : dans le set en cours)
 *   extPrev : par sets, time-outs des sets terminés, pour les rendre si une correction rouvre un set
 *   paused  : écran « Pause » affiché (aussi chez les spectateurs)
 *   rev     : version, incrémentée à chaque modification
 * À 3 joueurs (amicale), les séries tournent 1 → 2 → 3 ; pas de sets ni de chrono.
 * Il est gardé dans le navigateur (survit à un rechargement / une coupure Internet)
 * et poussé au serveur dès que possible ; la version la plus récente l'emporte.
 */
(function () {
    'use strict';

    const C = window.SB_CONFIG;
    const N = C.count || 2;
    const PLAYERS = N === 3 ? [1, 2, 3] : [1, 2];
    const S = C.starter;
    const O = S === 1 ? 2 : 1;
    const dist = (p) => C.distances[p];
    // Jeu par sets : nombre de sets gagnants (null en partie classique) ; dist() = points d'un set
    const SETS = C.sets;
    const STORE_KEY = 'sb_match_' + C.id + '_' + C.startedAt;

    const $ = (id) => document.getElementById(id);
    const root = $('sbd');
    const panels = {};
    PLAYERS.forEach(function (p) { panels[p] = $('sbdPlayer' + p); });
    const zeros = () => { const o = {}; PLAYERS.forEach(function (p) { o[p] = 0; }); return o; };

    let st = loadState();
    let buffer = '';
    let syncedRev = C.server.rev;
    let lastScores = {};
    // N° du set qui vient de se terminer, tant que l'arbitre n'a pas lancé le suivant (Entrée)
    let setBreak = null;

    // ------------------------------------------------------------ état

    function loadState() {
        const srv = C.server;
        let s = {
            rev: srv.rev,
            innings: srv.innings.slice(),
            run: srv.current_run,
            ext: srv.extensions.slice(),
            finished: srv.status === 'finished',
            paused: !!srv.paused,
        };
        if (C.spectator) return s;
        try {
            const local = JSON.parse(localStorage.getItem(STORE_KEY) || 'null');
            if (local && local.rev > s.rev) s = local;
        } catch (e) { /* stockage indisponible : on garde l'état serveur */ }
        return s;
    }

    // Le joueur qui commence le 1er set garde la bille blanche ; les joueurs commencent les sets à tour de rôle
    const setStarter = (n) => (n % 2 === 1 ? S : O);
    // Joueur de la k-ième série d'un set (k depuis 0) : rotation à partir de celui qui commence
    const seriesPlayer = (first, k) => (first - 1 + k) % N + 1;

    /*
     * Découpe les séries en reprises (set, n° de reprise dans le set, joueur).
     * Un set s'arrête dès qu'un joueur atteint ses points ; le dernier set reste le set courant.
     * Doit rester identique à ScoreboardMatchModel::walkInnings().
     */
    function walk() {
        const rows = [], won = { 1: 0, 2: 0 };
        let set = 1, inSet = 0, pts = zeros(), inn = zeros(), over = false;
        st.innings.forEach(function (v) {
            if (over) return;
            const p = seriesPlayer(setStarter(set), inSet);
            pts[p] += v; inn[p]++; inSet++;
            rows.push({ set: set, inning: inn[p], player: p, pts: v });
            if (SETS && pts[p] >= dist(p)) {
                won[p]++;
                if (won[p] >= SETS) { over = true; return; }
                set++; inSet = 0; pts = zeros(); inn = zeros();
            }
        });
        return { rows: rows, won: won, set: set, inSet: inSet };
    }

    function current() {
        const w = walk();
        return seriesPlayer(setStarter(w.set), w.inSet);
    }

    // Toute la partie (set = null) ou un seul set
    function totals(p, set) {
        let pts = 0, inn = 0, hr = 0;
        walk().rows.forEach(function (r) {
            if (r.player === p && (set == null || r.set === set)) { pts += r.pts; inn++; hr = Math.max(hr, r.pts); }
        });
        return { pts: pts, inn: inn, hr: hr };
    }

    // Score du set en cours (en partie classique : toute la partie)
    const setTotals = (p) => totals(p, walk().set);

    const remaining = () => dist(current()) - setTotals(current()).pts - st.run;

    // Reprise en cours (règles : dernière reprise, limite)
    function inningNumber() {
        const n = walk().inSet;
        return st.finished ? Math.ceil(n / N) : Math.floor(n / N) + 1;
    }

    // Reprises affichées : le compteur avance quand le joueur qui a commencé termine sa série (Entrée vers l'adversaire),
    // comme sur les marquoirs classiques — 0 avant la première série, puis 1 pendant que l'adversaire joue, etc.
    const inningsShown = () => Math.ceil(walk().inSet / N);

    const someoneReached = () => PLAYERS.some((p) => totals(p).pts >= dist(p));

    // Tour entamé alors qu'un joueur a déjà atteint ses points : les suivants terminent le tour
    function isEqualizing() {
        return !st.finished && !C.unequal && st.innings.length % N !== 0 && someoneReached();
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
            st.run = Math.min(parseInt(buffer, 10) || 0, dist(current()) - setTotals(current()).pts);
            buffer = '';
        }
        const setBefore = walk().set;
        st.innings.push(st.run);
        st.run = 0;
        clock.expired = false;
        checkEnd();
        const newSet = SETS && !st.finished && walk().set !== setBefore;
        if (newSet) {
            setBreak = setBefore;
            // Le nombre de time-outs choisi vaut pour chaque set : compteurs remis à zéro
            st.extPrev = (st.extPrev || []).concat([st.ext]);
            st.ext = [0, 0];
        }
        if (st.finished) {
            stopClock();
        } else if (C.clock.autoStart && !newSet) {
            restartClock();
        } else {
            resetClock();
        }
        commit();
    }

    function checkEnd() {
        if (SETS) {
            const w = walk();
            if (w.won[1] >= SETS || w.won[2] >= SETS) st.finished = true;
            return;
        }
        const n = st.innings.length;
        const reached = someoneReached();
        if (n % N !== 0) {
            // Tour pas terminé : sans égalisation, celui qui atteint ses points gagne immédiatement
            if (reached && C.unequal) st.finished = true;
        } else {
            if (reached) st.finished = true;
            if (C.inningsLimit && n / N >= C.inningsLimit) st.finished = true;
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
            const setBefore = walk().set;
            st.run = st.innings.pop();
            if (SETS && walk().set < setBefore) st.ext = (st.extPrev || []).pop() || [0, 0];
            clock.expired = false;
            stopClock();
        }
        commit();
    }

    // Écran « Pause » : reste affiché jusqu'au prochain appui sur « . » ; le chrono s'arrête
    function togglePause() {
        if (st.finished) return;
        st.paused = !st.paused;
        if (st.paused) stopClock();
        commit();
    }

    /*
     * Rejoue des séries corrigées depuis le début : null si la partie (ou un set) se serait arrêtée plus tôt,
     * ou si un joueur dépasserait ses points ; sinon indique si la partie est terminée.
     */
    function replay(arr) {
        const saved = st;
        try {
            let fin = false;
            for (let i = 0; i < arr.length; i++) {
                st = { innings: arr.slice(0, i + 1), run: 0, ext: saved.ext, finished: false };
                const w = walk();
                const r = w.rows[w.rows.length - 1];
                if (w.rows.length !== i + 1 || totals(r.player, r.set).pts > dist(r.player)) return null;
                checkEnd();
                if (st.finished && i < arr.length - 1) return null;
                fin = st.finished;
            }
            if (!fin) {
                st = { innings: arr, run: saved.run, ext: saved.ext, finished: false };
                if (setTotals(current()).pts + saved.run > dist(current())) return null;
            }
            return { finished: fin };
        } finally {
            st = saved;
        }
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
            body: JSON.stringify({ rev: st.rev, innings: st.innings, current_run: st.run, extensions: st.ext, finished: st.finished, paused: !!st.paused }),
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

    /*
     * Bille jouée par un joueur. À 2 : la sienne (1 = blanche, 2 = jaune). À 3, il n'y a que deux billes :
     * elles alternent d'une série à l'autre (A blanche, B jaune, C blanche, A jaune…). Les couleurs ne
     * changent qu'au début d'un tour (Entrée après le 3e joueur), pas à chaque passage de main.
     */
    function ballOf(p) {
        if (N === 2 || !p) return p || 1;
        const n = st.innings.length;
        const round = st.finished ? Math.floor(Math.max(0, n - 1) / N) : Math.floor(n / N);
        const k = round * N + (p - S + N) % N;
        return k % 2 === 0 ? 1 : 2;
    }
    const colorOf = (p) => 'var(--sb-p' + ballOf(p) + ')';

    const fmtAvg = (pts, inn) => (inn > 0 ? pts / inn : 0).toFixed(3).replace('.', ',');
    const name = (p) => C.players[p].last + ' ' + C.players[p].first;

    // Nom de famille trop long pour la case : police réduite juste assez pour l'afficher en entier
    function fitNames() {
        document.querySelectorAll('.sbd-last').forEach(function (el) {
            el.style.fontSize = '';
            const base = parseFloat(getComputedStyle(el).fontSize);
            let size = base;
            for (let i = 0; i < 6 && el.scrollWidth > el.clientWidth + 1 && size > base * 0.4; i++) {
                size = Math.max(base * 0.4, size * el.clientWidth / el.scrollWidth * 0.98);
                el.style.fontSize = size + 'px';
            }
        });
    }
    fitNames();
    window.addEventListener('resize', fitNames);
    document.addEventListener('fullscreenchange', fitNames);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitNames);

    // « Pour 5 … Pour 1 » (3 bandes : à partir de 3) quand il ne manque plus que quelques points au joueur à la table
    const POUR = C.discipline === '3bandes' ? [3, 2, 1] : [5, 4, 3, 2, 1];
    let pourKey = null, pourTimer = null;

    function announcePour(p, left) {
        const box = $('sbdPour');
        box.style.setProperty('--pc', colorOf(p));
        box.querySelector('small').textContent = C.players[p].last;
        box.querySelector('b').textContent = 'Pour ' + left;
        box.classList.remove('show'); void box.offsetWidth; box.classList.add('show');
        clearTimeout(pourTimer);
        pourTimer = setTimeout(function () { box.classList.remove('show'); }, 2200);
    }

    function checkPour(cur) {
        const left = cur ? remaining() : null;
        const key = cur + ':' + left;
        // Pas d'annonce au chargement de la page : seulement quand la situation change
        if (pourKey !== null && key !== pourKey && POUR.indexOf(left) !== -1) announcePour(cur, left);
        pourKey = key;
    }

    function render() {
        const cur = st.finished ? 0 : current();
        checkPour(cur);

        const w = walk();
        PLAYERS.forEach(function (p) {
            // La série en cours n'est ajoutée au score qu'à la validation de la reprise (Entrée).
            // Par sets : le grand chiffre est le score du set, moyenne et série max portent sur toute la partie.
            const t = totals(p);
            const ts = totals(p, w.set);
            const el = panels[p];
            el.classList.toggle('active', p === cur);
            if (N === 3) el.style.setProperty('--pc', colorOf(p));
            const score = el.querySelector('[data-score]');
            score.textContent = ts.pts;
            score.classList.toggle('long', ts.pts >= 100);
            if (lastScores[p] != null && lastScores[p] !== ts.pts) {
                score.classList.remove('bump'); void score.offsetWidth; score.classList.add('bump');
            }
            lastScores[p] = ts.pts;
            el.querySelector('[data-avg]').textContent = fmtAvg(t.pts, t.inn);
            el.querySelector('[data-hr]').textContent = t.hr;
            if (N === 2) renderLast5(p, el.querySelector('[data-last5]'), w);
            if (SETS) {
                let dots = '';
                for (let i = 0; i < SETS; i++) dots += '<i class="' + (i < w.won[p] ? 'fas' : 'far') + ' fa-circle"></i>';
                el.querySelector('[data-sets]').innerHTML = dots;
            }

            const ext = el.querySelector('[data-ext]');
            if (C.clock.enabled && C.clock.extensions > 0) {
                let html = '';
                for (let i = 0; i < C.clock.extensions; i++) {
                    html += '<i class="fas fa-stopwatch' + (i < st.ext[p - 1] ? ' used' : '') + '"></i>';
                }
                ext.innerHTML = html;
            }
        });

        if (SETS) {
            $('sbdSetNo').textContent = 'Set ' + w.set;
            $('sbdSetsScore').innerHTML = '<i style="color:var(--sb-p1)">' + w.won[1] + '</i> – <i style="color:var(--sb-p2)">' + w.won[2] + '</i>';
        }
        $('sbdInnings').textContent = inningsShown();
        $('sbdInningsLimit').textContent = C.inningsLimit ? 'sur ' + C.inningsLimit : '';

        const runBox = $('sbdRunBox');
        runBox.style.setProperty('--run-color', colorOf(cur));
        runBox.classList.toggle('typing', buffer !== '');
        $('sbdRun').textContent = buffer !== '' ? buffer : st.run;
        $('sbdHint').textContent = buffer !== '' ? 'Entrée pour valider la série' : '';

        let notice = flashMsg;
        if (clock.expired && !st.finished) notice = 'Temps écoulé\nEntrée pour passer la main';
        else if (notice) { /* message temporaire prioritaire */ }
        else if (isEqualizing()) notice = "Reprise d'égalisation";
        else if (SETS && !st.finished && w.set > 1 && w.inSet === 0 && st.run === 0) notice = C.players[setStarter(w.set)].last + ' commence le set ' + w.set;
        else if (!st.finished && C.inningsLimit && inningNumber() === C.inningsLimit) notice = 'Dernière reprise';
        $('sbdNotice').textContent = notice;

        renderSetBreak(w);
        $('sbdPause').classList.toggle('show', !!st.paused && !st.finished);
        renderResult();
        renderClock();
    }

    function setSummary(w, n) {
        const rows = w.rows.filter((r) => r.set === n);
        const sum = { 1: { pts: 0, inn: 0, hr: 0 }, 2: { pts: 0, inn: 0, hr: 0 } };
        rows.forEach(function (r) {
            const t = sum[r.player];
            t.pts += r.pts; t.inn++; t.hr = Math.max(t.hr, r.pts);
        });
        sum.winner = rows.length && sum[rows[rows.length - 1].player].pts >= dist(rows[rows.length - 1].player) ? rows[rows.length - 1].player : 0;
        return sum;
    }

    // Fin d'un set (hors fin de partie) : récapitulatif jusqu'à ce que l'arbitre lance le set suivant
    function renderSetBreak(w) {
        const box = $('sbdSetBreak');
        if (!box) return;
        if (setBreak !== null && (st.finished || w.set !== setBreak + 1 || w.inSet > 0 || st.run > 0)) setBreak = null;
        box.classList.toggle('open', setBreak !== null);
        if (setBreak === null) return;

        const sum = setSummary(w, setBreak);
        const next = setStarter(setBreak + 1);
        $('sbdSetBreakTitle').textContent = 'Fin du set ' + setBreak;
        $('sbdSetBreakWinner').textContent = sum.winner ? 'Set gagné par ' + name(sum.winner) : '';
        $('sbdSetBreakWinner').style.color = sum.winner === 2 ? 'var(--sb-p2)' : 'var(--sb-p1)';
        $('sbdSetBreakScore').innerHTML = $('sbdSetsScore').innerHTML;
        fillRows($('sbdSetBreakRows'), [1, 2].map((p) => [name(p), sum[p].pts, sum[p].inn, fmtAvg(sum[p].pts, sum[p].inn), sum[p].hr]));
        $('sbdSetBreakNext').textContent = C.players[next].last + ' commence le set ' + (setBreak + 1)
            + ' (bille ' + (next === 1 ? 'blanche' : 'jaune') + ')';
        $('sbdSetBreakKey').textContent = 'commencer le set ' + (setBreak + 1);
    }

    function fillRows(tbody, rows) {
        tbody.innerHTML = '';
        rows.forEach(function (cells, i) {
            const tr = document.createElement('tr');
            cells.forEach(function (v) {
                const td = document.createElement('td');
                td.textContent = v;
                tr.appendChild(td);
            });
            tr.style.color = i === 0 ? 'var(--sb-p1)' : 'var(--sb-p2)';
            tbody.appendChild(tr);
        });
    }

    // 5 dernières reprises terminées du joueur dans le set en cours, la plus récente en haut
    function renderLast5(p, box, w) {
        const mine = [];
        w.rows.forEach(function (r) { if (r.player === p && r.set === w.set) mine.push({ n: r.inning, pts: r.pts }); });
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

    /*
     * Même règle que ScoreboardMatchModel::winnerOf() : distance atteinte → pourcentage de la distance
     * (handicap) ; arrêt à la limite de reprises → moyenne proportionnelle (moyenne / moyenne minimum
     * de la catégorie), ou pourcentage de la distance si une catégorie manque.
     */
    function winnerOf(t1, t2) {
        const prop = (p, t) => (C.avgMin[p] > 0 && t.inn > 0 ? (t.pts / t.inn) / C.avgMin[p] : null);
        const p1 = prop(1, t1), p2 = prop(2, t2);
        const reached = t1.pts >= dist(1) || t2.pts >= dist(2);
        if (!reached && p1 !== null && p2 !== null) {
            const r = (v) => Math.round(v * 1e9);
            return { cmp: Math.sign(r(p1) - r(p2)), proportional: true, prop: { 1: p1, 2: p2 } };
        }
        return { cmp: Math.sign(t1.pts * dist(2) - t2.pts * dist(1)), proportional: false };
    }

    /*
     * Classement à 3 joueurs { ranks: {joueur: rang} } : même règle que winnerOf(), généralisée.
     * Doit rester identique à ScoreboardMatchModel::ranking().
     */
    function ranking(t) {
        const prop = {};
        PLAYERS.forEach(function (p) { prop[p] = C.avgMin[p] > 0 && t[p].inn > 0 ? (t[p].pts / t[p].inn) / C.avgMin[p] : null; });
        const reached = PLAYERS.some((p) => t[p].pts >= dist(p));
        const useProp = !reached && PLAYERS.every((p) => prop[p] !== null);
        const r = (v) => Math.round(v * 1e9);
        const cmp = (a, b) => (useProp ? Math.sign(r(prop[a]) - r(prop[b])) : Math.sign(t[a].pts * dist(b) - t[b].pts * dist(a)));
        const ranks = {};
        PLAYERS.forEach(function (a) { ranks[a] = 1 + PLAYERS.filter((b) => b !== a && cmp(b, a) > 0).length; });
        return { ranks: ranks, proportional: useProp, prop: prop };
    }

    function renderTrioResult() {
        const t = {};
        PLAYERS.forEach(function (p) { t[p] = totals(p); });
        const res = ranking(t);
        const firsts = PLAYERS.filter((p) => res.ranks[p] === 1);
        $('sbdWinner').textContent = firsts.length === 1
            ? 'Victoire de ' + name(firsts[0])
            : 'Ex æquo : ' + firsts.map((p) => C.players[p].last).join(' et ');
        $('sbdWinner').style.color = 'var(--sb-p2)';
        $('sbdDecision').hidden = !res.proportional;
        $('sbdPropHead').hidden = !res.proportional;

        const rows = $('sbdResultRows');
        rows.innerHTML = '';
        PLAYERS.slice().sort((a, b) => res.ranks[a] - res.ranks[b] || a - b).forEach(function (p) {
            const rank = res.ranks[p];
            const cells = [(rank === 1 ? '1er' : rank + 'e') + '  ' + name(p), t[p].pts, t[p].inn, fmtAvg(t[p].pts, t[p].inn), t[p].hr];
            if (res.proportional) cells.push(res.prop[p].toFixed(3).replace('.', ','));
            const tr = document.createElement('tr');
            cells.forEach(function (v) {
                const td = document.createElement('td');
                td.textContent = v;
                tr.appendChild(td);
            });
            tr.style.color = rank === 1 ? 'var(--sb-p2)' : '#fff';
            rows.appendChild(tr);
        });
    }

    function renderResult() {
        const box = $('sbdResult');
        box.classList.toggle('open', st.finished);
        if (!st.finished) return;
        if (SETS) { renderSetsResult(); return; }
        if (N === 3) { renderTrioResult(); return; }

        const t1 = totals(1), t2 = totals(2);
        const res = winnerOf(t1, t2);
        const cmp = res.cmp;
        $('sbdDecision').hidden = !res.proportional;
        $('sbdPropHead').hidden = !res.proportional;
        $('sbdWinner').textContent = cmp === 0 ? 'Match nul' : 'Victoire de ' + name(cmp > 0 ? 1 : 2);
        $('sbdWinner').style.color = cmp === 0 ? '#fff' : (cmp > 0 ? 'var(--sb-p1)' : 'var(--sb-p2)');

        const rows = $('sbdResultRows');
        rows.innerHTML = '';
        [[1, t1], [2, t2]].forEach(function (pair) {
            const tr = document.createElement('tr');
            const cells = [name(pair[0]), pair[1].pts, pair[1].inn, fmtAvg(pair[1].pts, pair[1].inn), pair[1].hr];
            if (res.proportional) cells.push(res.prop[pair[0]].toFixed(3).replace('.', ','));
            cells.forEach(function (v) {
                const td = document.createElement('td');
                td.textContent = v;
                tr.appendChild(td);
            });
            tr.style.color = pair[0] === 1 ? 'var(--sb-p1)' : 'var(--sb-p2)';
            rows.appendChild(tr);
        });
    }

    // Par sets : une colonne par set (points et reprises), sets gagnés, moyenne générale et série max
    function renderSetsResult() {
        const w = walk();
        const cmp = Math.sign(w.won[1] - w.won[2]);
        $('sbdWinner').textContent = cmp === 0 ? 'Match nul' : 'Victoire de ' + name(cmp > 0 ? 1 : 2);
        $('sbdWinner').style.color = cmp === 0 ? '#fff' : (cmp > 0 ? 'var(--sb-p1)' : 'var(--sb-p2)');
        $('sbdDecision').hidden = false;
        $('sbdDecision').textContent = Math.max(w.won[1], w.won[2]) + ' sets à ' + Math.min(w.won[1], w.won[2]);

        const sums = [];
        for (let n = 1; n <= w.set; n++) sums.push(setSummary(w, n));
        let head = '<th></th>';
        sums.forEach(function (x, i) { head += '<th>Set ' + (i + 1) + '</th>'; });
        $('sbdResultHead').innerHTML = head + '<th>Sets</th><th>Moyenne</th><th>MS</th>';

        fillRows($('sbdResultRows'), [1, 2].map(function (p) {
            const t = totals(p);
            const cells = [name(p)];
            sums.forEach(function (x) { cells.push(x[p].pts + ' (' + x[p].inn + ')'); });
            return cells.concat([w.won[p], fmtAvg(t.pts, t.inn), t.hr]);
        }));
    }

    // ------------------------------------------------------------ clavier / pavé numérique

    function actionFor(e) {
        const code = e.code, key = e.key;
        if (code === 'NumpadAdd' || key === '+') return 'plus';
        if (code === 'NumpadSubtract' || key === '-') return 'minus';
        if (code === 'NumpadEnter' || code === 'Enter' || key === 'Enter') return 'enter';
        if (code === 'NumpadMultiply' || key === '*') return 'clock';
        if (code === 'NumpadDivide' || key === '/') return 'clear';
        if (code === 'NumpadDecimal' || key === '.' || key === ',') return 'pause';
        // Retour arrière du pavé : le menu reste accessible sans le clavier du Chromebook
        if (key === 'Backspace' || key === 'Escape') return 'menu';
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
            $('sbdFixBtn').hidden = st.innings.length === 0 || C.signed;
            // Feuille signée : plus aucune modification au marquoir
            menu.querySelector('[data-action="edit"]').hidden = C.signed;
            $('sbdCancelForm').hidden = C.signed;
            menu.classList.add('open');
            root.classList.add('show-cursor');
            focusMenu(0);
        };
        swapForm.addEventListener('submit', function () {
            try { localStorage.removeItem(STORE_KEY); } catch (e) { /* ignoré */ }
        });
        const closeMenu = () => { menu.classList.remove('open'); };

        // Navigation au pavé dans le menu : 8 / 2 pour se déplacer, Entrée pour valider
        let menuIndex = 0;
        const menuItems = () => [...menu.querySelectorAll('.btn')].filter((b) => b.offsetParent !== null);
        function focusMenu(i) {
            const items = menuItems();
            if (!items.length) return;
            menuIndex = (i + items.length) % items.length;
            items.forEach((b, k) => b.classList.toggle('kbd-focus', k === menuIndex));
            items[menuIndex].focus({ preventScroll: true });
        }

        // Boîte de confirmation au pavé : 4 / 6 (ou 8 / 2) changent de bouton, Retour arrière répond « non »
        function swalKey(e, a) {
            if (a === 'menu') { e.preventDefault(); e.stopPropagation(); Swal.clickCancel(); return; }
            if (['d2', 'd4', 'd6', 'd8'].indexOf(a) === -1) return;
            // La boîte gère déjà les flèches (Verr. Num désactivé) : on évite un double changement
            e.preventDefault();
            e.stopPropagation();
            const ok = Swal.getConfirmButton(), no = Swal.getCancelButton();
            (document.activeElement === ok ? no : ok).focus();
        }

        // ---------------------------------------------- correction d'une reprise quelconque

        // idx : série sélectionnée (index dans st.innings) ; edit : saisie en cours (null = navigation)
        const fix = { open: false, idx: 0, edit: null, msg: '', err: false };

        // Lignes du tableau : une par reprise (et par set), avec l'index de la série de chaque joueur
        function fixRows() {
            const rows = [], byKey = {};
            walk().rows.forEach(function (r, i) {
                const key = r.set + ':' + r.inning;
                if (!byKey[key]) { byKey[key] = { set: r.set, inning: r.inning, cells: {} }; rows.push(byKey[key]); }
                byKey[key].cells[r.player] = i;
            });
            return rows;
        }

        function openFix() {
            if (!st.innings.length) return;
            Object.assign(fix, { open: true, idx: st.innings.length - 1, edit: null, msg: '', err: false });
            renderFix();
        }

        function closeFix() {
            fix.open = false;
            $('sbdFix').classList.remove('open');
        }

        function moveFix(dRow, dCol) {
            const rows = fixRows();
            let r = rows.findIndex((x) => PLAYERS.some((q) => x.cells[q] === fix.idx));
            const p = PLAYERS.find((q) => rows[r].cells[q] === fix.idx);
            if (dCol) {
                const present = PLAYERS.filter((q) => rows[r].cells[q] !== undefined);
                const j = present.indexOf(p);
                fix.idx = rows[r].cells[present[(j + dCol + present.length) % present.length]];
            } else {
                r = Math.max(0, Math.min(rows.length - 1, r + dRow));
                fix.idx = rows[r].cells[p] !== undefined ? rows[r].cells[p] : rows[r].cells[PLAYERS.find((q) => rows[r].cells[q] !== undefined)];
            }
            fix.msg = '';
        }

        function applyFix() {
            const arr = st.innings.slice();
            arr[fix.idx] = parseInt(fix.edit || '0', 10);
            const res = replay(arr);
            if (!res) {
                fix.msg = 'Impossible : avec cette série, un joueur dépasserait ses points ou la partie se serait arrêtée plus tôt.';
                fix.err = true;
                return;
            }
            st.innings = arr;
            st.finished = res.finished;
            if (st.finished) { st.run = 0; stopClock(); }
            setBreak = null;
            fix.edit = null;
            fix.msg = 'Reprise corrigée.';
            fix.err = false;
            commit();
        }

        function fixKey(a) {
            if (fix.edit !== null) {
                if (a === 'enter') applyFix();
                else if (a === 'menu') fix.edit = null;
                else if (a === 'clear') fix.edit = '';
                else if (a.charAt(0) === 'd' && fix.edit.length < 3) fix.edit = (fix.edit === '0' ? '' : fix.edit) + a.slice(1);
            } else if (a === 'menu') {
                closeFix();
                return;
            } else if (a === 'enter') {
                fix.edit = '';
                fix.msg = '';
            } else if (a === 'd8') moveFix(-1, 0);
            else if (a === 'd2') moveFix(1, 0);
            else if (a === 'd4') moveFix(0, -1);
            else if (a === 'd6') moveFix(0, 1);
            renderFix();
        }

        function renderFix() {
            if (!fix.open) return;
            $('sbdFix').classList.add('open');
            const tbody = $('sbdFixRows');
            let html = '', lastSet = 0;
            fixRows().forEach(function (row) {
                if (SETS && row.set !== lastSet) {
                    html += '<tr class="sbd-fix-set"><td colspan="' + (N + 1) + '">Set ' + row.set + '</td></tr>';
                    lastSet = row.set;
                }
                html += '<tr><td>' + row.inning + '</td>';
                PLAYERS.forEach(function (p) {
                    const i = row.cells[p];
                    if (i === undefined) { html += '<td class="empty">–</td>'; return; }
                    const sel = i === fix.idx;
                    const val = sel && fix.edit !== null ? (fix.edit === '' ? '_' : fix.edit) : st.innings[i];
                    html += '<td class="' + (sel ? 'sel' + (fix.edit !== null ? ' editing' : '') : '') + '">' + val + '</td>';
                });
                html += '</tr>';
            });
            tbody.innerHTML = html;
            const sel = tbody.querySelector('.sel');
            if (sel) sel.scrollIntoView({ block: 'nearest' });
            const msg = $('sbdFixMsg');
            msg.textContent = fix.msg;
            msg.classList.toggle('err', fix.err);
            $('sbdFixHelp').innerHTML = fix.edit !== null
                ? '<kbd>0-9</kbd> nouvelle série &nbsp;·&nbsp; <kbd>Entrée</kbd> valider &nbsp;·&nbsp; <kbd>/</kbd> effacer &nbsp;·&nbsp; <kbd>⌫</kbd> annuler'
                : '<kbd>8</kbd> <kbd>2</kbd> reprise &nbsp;·&nbsp; <kbd>4</kbd> <kbd>6</kbd> joueur &nbsp;·&nbsp; <kbd>Entrée</kbd> corriger &nbsp;·&nbsp; <kbd>⌫</kbd> fermer';
        }

        // Phase de capture sur window : la boîte de confirmation intercepte sinon les touches avant nous
        window.addEventListener('keydown', function (e) {
            const a = actionFor(e);
            // Entrée et Échap sont gérés par la boîte de confirmation elle-même
            if (window.Swal && Swal.isVisible()) { swalKey(e, a); return; }
            if (!a) return;
            e.preventDefault();

            if (fix.open) {
                if (!e.repeat) fixKey(a);
                return;
            }
            if (menu.classList.contains('open')) {
                if (e.repeat) return;
                if (a === 'menu') closeMenu();
                else if (a === 'd8') focusMenu(menuIndex - 1);
                else if (a === 'd2') focusMenu(menuIndex + 1);
                else if (a === 'enter') { const b = menuItems()[menuIndex]; if (b) b.click(); }
                return;
            }
            if (e.repeat && a !== 'plus' && a !== 'minus') return;
            enterFullscreen();

            if (a === 'pause') { togglePause(); return; }
            if (st.paused) return;

            if (setBreak !== null) {
                if (a === 'enter') {
                    setBreak = null;
                    if (C.clock.autoStart) restartClock();
                    render();
                }
                else if (a === 'minus') { setBreak = null; minus(); }
                else if (a === 'menu') openMenu();
                return;
            }

            if (st.finished) {
                if (a === 'enter') window.location.href = C.urls.table;
                else if (a === 'minus' && !C.signed) minus();
                else if (a === 'plus' && !e.repeat && C.urls.rematch) confirmRematch();
                else if (a === 'plus' && !e.repeat && C.urls.sign) goSign();
                else if (a === 'menu') openMenu();
                return;
            }

            switch (a) {
                case 'plus':  addPoint(); break;
                case 'minus': minus(); break;
                case 'enter': endInning(); break;
                case 'clock': toggleClock(); break;
                case 'clear': clearBuffer(); break;
                case 'menu':  openMenu(); break;
                default:      digit(a.slice(1));
            }
        }, true);

        menu.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action]');
            if (e.target === menu || (btn && btn.dataset.action === 'close')) closeMenu();
            if (btn && btn.dataset.action === 'fullscreen') { enterFullscreen(); closeMenu(); }
            if (btn && btn.dataset.action === 'cancel') confirmCancel();
            if (btn && btn.dataset.action === 'fix') { closeMenu(); openFix(); }
            if (btn && btn.dataset.action === 'edit') {
                // Les dernières reprises doivent être enregistrées avant de quitter l'écran
                btn.disabled = true;
                waitSynced(8000).then(function () { window.location.href = C.urls.edit; }, function () {
                    btn.disabled = false;
                    closeMenu();
                    flash('Pas de connexion :\nréessayez dans un instant');
                    render();
                });
            }
        });

        // Le résultat doit être enregistré sur le serveur avant de quitter la page
        function waitSynced(timeoutMs) {
            sync();
            const until = Date.now() + timeoutMs;
            return new Promise(function (resolve, reject) {
                (function check() {
                    if (st.rev <= syncedRev) return resolve();
                    if (Date.now() > until) return reject();
                    setTimeout(check, 200);
                })();
            });
        }

        // Le résultat doit être sur le serveur avant d'être signé
        let signing = false;
        function goSign() {
            if (signing) return;
            signing = true;
            const btn = $('sbdSignBtn');
            if (btn) btn.disabled = true;
            waitSynced(8000).then(function () { window.location.href = C.urls.sign; }, function () {
                signing = false;
                if (btn) btn.disabled = false;
                flash('Pas de connexion :\nréessayez dans un instant');
                render();
            });
        }
        if ($('sbdSignBtn')) $('sbdSignBtn').addEventListener('click', goSign);

        function confirmRematch() {
            const form = $('sbdRematchForm');
            if (!form) return;
            if (!window.Swal) { waitSynced(5000).then(function () { form.submit(); }, function () {}); return; }
            Swal.fire({
                title: 'Rejouer la même partie ?',
                html: 'Mêmes joueurs, même mode de jeu.<br><kbd>Entrée</kbd> oui &nbsp;·&nbsp; <kbd>⌫</kbd> non',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, rejouer',
                cancelButtonText: 'Non',
                reverseButtons: true,
                focusConfirm: true,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: function () {
                    return waitSynced(8000).catch(function () {
                        Swal.showValidationMessage("Résultat pas encore enregistré (connexion ?). Réessayez dans un instant.");
                        return false;
                    });
                },
                background: '#16213d',
                color: '#eef1f7',
                iconColor: '#38bdf8',
                confirmButtonColor: '#84252B',
                cancelButtonColor: '#475569',
                customClass: { popup: 'sbd-swal' },
            }).then(function (r) {
                if (!r.isConfirmed) return;
                try { localStorage.removeItem(STORE_KEY); } catch (e) { /* ignoré */ }
                form.submit();
            });
        }

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
                footer: '<span class="sbd-swal-keys"><kbd>4</kbd> <kbd>6</kbd> choisir &nbsp;·&nbsp; <kbd>Entrée</kbd> valider &nbsp;·&nbsp; <kbd>⌫</kbd> non</span>',
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
                if (d.rev !== st.rev || (d.status === 'finished') !== st.finished || !!d.paused !== !!st.paused) {
                    st = { rev: d.rev, innings: d.innings.slice(), run: d.current_run, ext: d.extensions.slice(), finished: d.status === 'finished', paused: !!d.paused };
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
