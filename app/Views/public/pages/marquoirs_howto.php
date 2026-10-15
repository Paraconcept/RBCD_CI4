<?php $img = static fn (string $f): string => base_url('assets/images/marquoirs-howto/' . $f . '.jpg'); ?>
<?= $this->extend('public/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700;800&display=swap">
<style>
/* ── Mode d'emploi des marquoirs (couleurs du module marquoir) ── */
.mh { --navy: #0a1020; --red: #84252B; --yellow: #ffcc00; --soft: #f4f6fb; --muted: #5d677c;
      font-family: 'Barlow', sans-serif; color: #1b2233; font-size: 1.05rem; }
.mh p, .mh li, .mh div, .mh span { font-family: inherit; }
.mh p, .mh li { line-height: 1.55; }
.mh b, .mh strong { font-weight: 700; color: inherit; }
.mh-intro { background: radial-gradient(ellipse at 70% 0%, #1d2f5c 0%, var(--navy) 60%); color: #fff; border-radius: 14px; padding: 2.2rem; border-top: 5px solid var(--red); }
.mh-intro .kicker { font-family: 'Barlow Condensed'; letter-spacing: .22em; text-transform: uppercase; color: #aeb7cc; font-weight: 600; }
.mh-intro h2 { font-family: 'Barlow Condensed'; font-weight: 800; font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: .95; color: #fff; margin: .4rem 0 1rem; }
.mh-intro h2 span { color: var(--yellow); }
.mh-intro p { color: #d7dceb; font-size: 1.1rem; }
.mh-intro .tags span { display: inline-block; border: 1px solid #3a4a73; border-radius: 20px; padding: .3rem .9rem; margin: .25rem .3rem 0 0; color: #d7dceb; font-size: .95rem; }
.mh-intro .tags i { color: var(--yellow); margin-right: .35rem; }
.mh-pdf { background: var(--red); border-color: var(--red); color: #fff; font-weight: 600; }
.mh-pdf:hover, .mh-pdf:focus { background: #b8343c; border-color: #b8343c; color: #fff; }

.mh-toc { display: flex; flex-wrap: wrap; gap: .5rem; margin: 1.8rem 0 0; }
.mh-toc a { background: var(--soft); border-radius: 20px; padding: .35rem .95rem; color: var(--navy); font-weight: 600; text-decoration: none; font-size: .95rem; }
.mh-toc a:hover { background: var(--navy); color: #fff; }

.mh-section { margin-top: 3.2rem; scroll-margin-top: 110px; }
.mh-head { background: var(--navy); color: #fff; border-radius: 12px 12px 0 0; border-bottom: 5px solid var(--red); padding: 1rem 1.4rem; display: flex; align-items: center; gap: 1rem; }
.mh-head .num { width: 48px; height: 48px; border-radius: 50%; background: var(--red); display: grid; place-items: center; font-family: 'Barlow Condensed'; font-weight: 800; font-size: 1.6rem; flex: none; }
.mh-head h3 { font-family: 'Barlow Condensed'; font-weight: 700; font-size: clamp(1.5rem, 3vw, 2.1rem); color: #fff; margin: 0; line-height: 1.05; }
.mh-head p { margin: .2rem 0 0; color: #aeb7cc; font-size: .98rem; }
.mh-body { border: 1px solid #e3e7ef; border-top: none; border-radius: 0 0 12px 12px; padding: 1.6rem 1.4rem; }

.mh-shot { width: 100%; border-radius: 10px; border: 2px solid var(--navy); display: block; }
.mh-phone { width: 100%; border-radius: 18px; border: 7px solid #11141d; display: block; }
.mh-caption { text-align: center; color: var(--muted); font-size: .92rem; margin-top: .5rem; }
.mh h4 { font-family: 'Barlow Condensed'; font-weight: 700; color: var(--navy); font-size: 1.45rem; margin: 0 0 .3rem; }
.mh h4 i { color: var(--red); margin-right: .4rem; }

.mh-steps { list-style: none; counter-reset: step; padding: 0; margin: 0; }
.mh-steps li { position: relative; padding-left: 2.4rem; margin-bottom: .8rem; }
.mh-steps li::before { counter-increment: step; content: counter(step); position: absolute; left: 0; top: .05rem; width: 1.65rem; height: 1.65rem; border-radius: 50%; background: var(--red); color: #fff; font-family: 'Barlow Condensed'; font-weight: 700; display: grid; place-items: center; }
.mh-steps ul { margin: .4rem 0 0; padding-left: 1.2rem; }
.mh-steps ul li { padding-left: 0; margin-bottom: .2rem; }
.mh-steps ul li::before { content: none; }

.mh kbd { background: var(--navy); color: #fff; border-radius: 5px; padding: .05rem .45rem; font-family: 'Barlow Condensed'; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 0 #000; }
.mh-note { background: var(--soft); border-left: 5px solid var(--red); border-radius: 0 8px 8px 0; padding: .8rem 1rem; margin-bottom: .8rem; }
.mh-note.green { border-left-color: #22c55e; }
.mh-note.orange { border-left-color: #f59e0b; }

.mh-formula { background: var(--navy); color: #fff; border-radius: 10px; padding: 1rem 1.2rem; text-align: center; font-size: 1.1rem; margin-bottom: 1rem; }
.mh-formula b { color: var(--yellow); }
.mh-formula small { display: block; color: #aeb7cc; margin-top: .3rem; }
.mh-ex { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
.mh-ex th, .mh-ex td { padding: .45rem .5rem; border-bottom: 1px solid #e3e7ef; text-align: center; }
.mh-ex th { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
.mh-ex td:first-child, .mh-ex th:first-child { text-align: left; }
.mh-ex tr.win td { font-weight: 700; color: var(--red); }

.mh-card { background: var(--soft); border-top: 5px solid var(--red); border-radius: 10px; padding: 1.2rem; height: 100%; }
.mh-card .ico { width: 46px; height: 46px; border-radius: 10px; background: var(--navy); color: var(--yellow); display: grid; place-items: center; font-size: 1.3rem; margin-bottom: .7rem; }
.mh-card h5 { font-family: 'Barlow Condensed'; font-weight: 700; color: var(--navy); font-size: 1.35rem; margin: 0 0 .3rem; }

/* Pavé numérique */
.mh-pad { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: minmax(64px, auto); gap: 8px; background: #1c2233; padding: 14px; border-radius: 16px; max-width: 340px; margin: 0 auto; }
.mh-key { border-radius: 9px; background: #2f4a7a; color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Barlow Condensed'; font-weight: 800; font-size: 1.6rem; line-height: 1; box-shadow: inset 0 -4px 0 rgba(0,0,0,.35); padding: 6px 2px; text-align: center; }
.mh-key small { font-family: 'Barlow'; font-weight: 600; font-size: .62rem; margin-top: 3px; line-height: 1.05; }
.mh-key.off { background: #2a3043; color: #6f7891; font-size: 1rem; }
.mh-key.plus { background: #16a34a; }
.mh-key.minus { background: #d97706; }
.mh-key.enter { background: var(--red); grid-row: span 2; }
.mh-key.star { background: #0284c7; }
.mh-key.slash { background: #64748b; }
.mh-key.menu { background: #0d9488; }
.mh-key.pause { background: #eab308; color: #0a1020; }
.mh-key.zero { grid-column: span 2; }
.mh-key.bar { background: #fde047; color: #0a1020; }
.mh-key.bar svg { width: 1.7rem; height: 1.7rem; }
.mh-legend .sw svg { width: 1.2rem; height: 1.2rem; }
.mh-legend { display: grid; grid-template-columns: 64px 1fr; gap: .7rem .9rem; align-items: center; }
.mh-legend .sw { height: 38px; border-radius: 8px; color: #fff; display: grid; place-items: center; font-family: 'Barlow Condensed'; font-weight: 800; font-size: 1.2rem; }
.mh-legend b { font-family: 'Barlow Condensed'; font-size: 1.2rem; color: var(--navy); display: block; line-height: 1.1; }

/* Écran annoté */
.mh-annot { position: relative; }
.mh-pin { position: absolute; width: 30px; height: 30px; border-radius: 50%; background: var(--yellow); color: var(--navy); border: 3px solid var(--navy); font-family: 'Barlow Condensed'; font-weight: 800; display: grid; place-items: center; transform: translate(-50%, -50%); font-size: 1rem; }
.mh-pins-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .6rem 1.4rem; margin-top: 1.2rem; }
.mh-pins-list div { display: grid; grid-template-columns: 32px 1fr; gap: .5rem; align-items: start; }
.mh-pins-list .n { width: 28px; height: 28px; border-radius: 50%; background: var(--yellow); color: var(--navy); border: 3px solid var(--navy); font-family: 'Barlow Condensed'; font-weight: 800; display: grid; place-items: center; font-size: .95rem; }
@media (max-width: 575.98px) { .mh-pin { width: 22px; height: 22px; font-size: .75rem; border-width: 2px; } .mh-body { padding: 1.2rem .9rem; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section-padding">
  <div class="container mh">

    <!-- Introduction -->
    <div class="mh-intro">
      <div class="row align-items-center g-4">
        <div class="col-lg-5">
          <div class="kicker">RBC Disonais · Mode d'emploi</div>
          <h2>Les nouveaux <span>marquoirs</span></h2>
          <p>Lancer une partie, arbitrer au pavé numérique et suivre les rencontres du club en direct : tout ce qu'il faut savoir.</p>
          <div class="tags mb-3">
            <span><i class="fas fa-tv"></i>Un écran par billard</span>
            <span><i class="fas fa-calculator"></i>Pavé numérique</span>
            <span><i class="fas fa-broadcast-tower"></i>En direct</span>
          </div>
          <a href="<?= base_url('assets/docs/Marquoirs_RBCD_mode_emploi.pdf') ?>" target="_blank" class="btn mh-pdf mt-2">
            <i class="far fa-file-pdf me-2"></i>Télécharger la version PDF (à imprimer)
          </a>
        </div>
        <div class="col-lg-7"><img class="mh-shot" src="<?= $img('06_marquoir') ?>" alt="L'écran du marquoir" style="border-color:#2a3a63"></div>
      </div>
    </div>

    <nav class="mh-toc" aria-label="Sommaire">
      <a href="#en-bref">En bref</a>
      <a href="#billard">1. Le billard</a>
      <a href="#amicale">2. Partie amicale</a>
      <a href="#echauffement">3. Échauffement</a>
      <a href="#pave">4. Le pavé numérique</a>
      <a href="#ecran">Lire l'écran</a>
      <a href="#chrono">Chrono et time-outs</a>
      <a href="#fin">Fin de partie</a>
      <a href="#corrections">Pause et corrections</a>
      <a href="#signature">Signer la feuille</a>
      <a href="#cloture">Rencontre d'équipe</a>
      <a href="#proportionnelle">Moyenne proportionnelle</a>
      <a href="#sets">Partie par sets</a>
      <a href="#trois">Partie à 3</a>
      <a href="#direct">En direct</a>
    </nav>

    <!-- En bref -->
    <div class="mh-section" id="en-bref">
      <div class="mh-head"><div class="num"><i class="fas fa-route"></i></div><div><h3>En bref</h3><p>Une partie se déroule toujours en quatre temps</p></div></div>
      <div class="mh-body">
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-lg-3"><div class="mh-card"><div class="ico"><i class="fas fa-th-large"></i></div><h5>1. Le billard</h5><p class="mb-0">Sur le Chromebook du billard, ouvrez <b>Marquoirs</b> et choisissez votre billard (souvent déjà en favori).</p></div></div>
          <div class="col-sm-6 col-lg-3"><div class="mh-card"><div class="ico"><i class="fas fa-play"></i></div><h5>2. La partie</h5><p class="mb-0">Démarrez la <b>rencontre du jour</b> préparée par le DS, ou lancez une <b>partie amicale</b>, à 2 ou à 3 joueurs.</p></div></div>
          <div class="col-sm-6 col-lg-3"><div class="mh-card"><div class="ico"><i class="fas fa-stopwatch"></i></div><h5>3. L'échauffement</h5><p class="mb-0">Deux échauffements de <b>5 minutes</b>, puis on choisit <b>qui commence</b> la partie (à 3 : pas d'échauffement).</p></div></div>
          <div class="col-sm-6 col-lg-3"><div class="mh-card"><div class="ico"><i class="fas fa-calculator"></i></div><h5>4. L'arbitrage</h5><p class="mb-0">L'arbitre marque les points au <b>pavé numérique</b>. Tout s'affiche sur la TV.</p></div></div>
        </div>
        <div class="row g-4 align-items-start">
          <div class="col-lg-7"><img class="mh-shot" src="<?= $img('01_billards') ?>" alt="Choix du billard"><div class="mh-caption">L'écran de départ : un bouton par billard. Le badge indique s'il est libre ou si une partie est en cours.</div></div>
          <div class="col-lg-5">
            <div class="mh-note green"><b>Code d'accès</b> — chaque Chromebook a été autorisé une fois pour toutes par le DS. Si le Chromebook affiche la page d'accueil du site au lieu du marquoir, il n'est plus autorisé : adressez-vous à un membre du comité.</div>
            <div class="mh-note"><b>Tout est enregistré</b> — chaque point est sauvegardé immédiatement. Même en cas de coupure Internet, la partie continue : les points sont envoyés dès le retour de la connexion.</div>
            <div class="mh-note orange"><b>Un clic suffit</b> — la souris ne sert qu'à démarrer. Ensuite, l'arbitre n'utilise plus que le pavé numérique.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- 1. Billard -->
    <div class="mh-section" id="billard">
      <div class="mh-head"><div class="num">1</div><div><h3>La page de votre billard</h3><p>Les rencontres prévues et les boutons « Partie amicale »</p></div></div>
      <div class="mh-body">
        <div class="row g-4">
          <div class="col-lg-7"><img class="mh-shot" src="<?= $img('02_billard') ?>" alt="Page d'un billard"></div>
          <div class="col-lg-5">
            <h4><i class="fas fa-calendar-check"></i>Rencontres du jour</h4>
            <p>Les rencontres officielles encodées par le DS apparaissent ici le jour même. Cliquez sur <b>Démarrer</b>.</p>
            <div class="mh-note orange">Le bouton se déverrouille <b>une heure avant</b> l'heure prévue : on ne peut pas lancer une rencontre officielle par erreur l'après-midi.</div>
            <h4 class="mt-3"><i class="fas fa-calendar-alt"></i>Rencontres programmées</h4>
            <p>Les prochaines rencontres sont affichées pour information. Elles ne peuvent pas encore être démarrées.</p>
            <h4 class="mt-3"><i class="fas fa-handshake"></i>Partie amicale</h4>
            <p>Pour un entraînement ou une partie entre membres, cliquez sur le grand bouton <b>Partie amicale</b>.</p>
            <h4 class="mt-3"><i class="fas fa-users"></i>Partie amicale à 3</h4>
            <p class="mb-0">Trois joueurs qui jouent à tour de rôle : bouton <b>Partie amicale à 3</b> (<a href="#trois">voir plus bas</a>).</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Amicale -->
    <div class="mh-section" id="amicale">
      <div class="mh-head"><div class="num">2</div><div><h3>Lancer une partie amicale</h3><p>Un seul écran à remplir</p></div></div>
      <div class="mh-body">
        <div class="row g-4">
          <div class="col-lg-7"><img class="mh-shot" src="<?= $img('03_amicale') ?>" alt="Formulaire de partie amicale"></div>
          <div class="col-lg-5">
            <ol class="mh-steps">
              <li><b>Les joueurs</b> — tapez les deux premières lettres du nom : les membres du club et les joueurs déjà rencontrés sont proposés. Un clic remplit le nom et le prénom.</li>
              <li><b>Points</b> à réaliser — propres à chaque joueur (handicap possible). Ceux du joueur 1 sont recopiés chez le joueur 2.</li>
              <li><b>Discipline et format</b> — libre, cadre, bande ou 3 bandes ; petit ou grand billard.</li>
              <li><b>Options</b> si besoin :
                <ul>
                  <li><b>Limite de reprises</b> (60 proposé en 3 bandes)</li>
                  <li><b>Reprises inégales</b> : pas de reprise d'égalisation</li>
                  <li><b>Chrono par coup</b> : 40 s et 3 time-outs par joueur</li>
                  <li><b>Par sets</b> (3 bandes) : <a href="#sets">voir plus bas</a></li>
                </ul>
              </li>
              <li>Cliquez sur <b>Continuer</b>.</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Échauffement -->
    <div class="mh-section" id="echauffement">
      <div class="mh-head"><div class="num">3</div><div><h3>Échauffement et choix du joueur qui commence</h3><p>Avant chaque partie</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('05_echauffement') ?>" alt="Échauffement"><div class="mh-caption">Le décompte de 5 minutes, en plein écran. La dernière minute s'affiche en rouge.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('04_qui_commence') ?>" alt="Qui commence la partie ?"><div class="mh-caption">L'écran « Qui commence la partie ? »</div></div>
        </div>
        <ol class="mh-steps">
          <li>Appuyez sur <kbd>*</kbd> pour lancer le <b>1<sup>er</sup> échauffement</b>.</li>
          <li>À la fin (ou avant), <kbd>Entrée</kbd> ramène à l'écran de choix. Recommencez pour le <b>2<sup>e</sup> échauffement</b>.</li>
          <li>Appuyez sur <kbd>1</kbd> ou <kbd>2</kbd> pour désigner <b>le joueur qui commence</b>.</li>
          <li>Il se place automatiquement <b>à gauche, en blanc</b>. Son adversaire joue à droite, en jaune.</li>
          <li><b>Mauvais joueur ?</b> Appuyez sur <kbd>⌫</kbd> pour ouvrir le menu, puis choisissez <b>« Inverser les deux joueurs »</b> (possible tant qu'aucun coup n'a été joué).</li>
        </ol>
      </div>
    </div>

    <!-- 4. Pavé -->
    <div class="mh-section" id="pave">
      <div class="mh-head"><div class="num">4</div><div><h3>Arbitrer au pavé numérique</h3><p>Toutes les touches utiles, d'un coup d'œil</p></div></div>
      <div class="mh-body">
        <div class="row g-4 align-items-start">
          <div class="col-lg-4">
            <div class="mh-pad">
              <div class="mh-key bar"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5.5 2h13l-.5 6.3a6 6 0 0 1-11.9 0z"/><rect x="11" y="13.6" width="2" height="5.6"/><path d="M6.8 22c0-1.3 2.3-2.3 5.2-2.3s5.2 1 5.2 2.3z"/></svg><small>appel bar</small></div>
              <div class="mh-key slash">/<small>effacer</small></div>
              <div class="mh-key star">*<small>chrono</small></div>
              <div class="mh-key menu">⌫<small>menu</small></div>
              <div class="mh-key">7</div><div class="mh-key">8<small><i class="fas fa-arrow-up"></i></small></div><div class="mh-key">9</div>
              <div class="mh-key minus">−<small>corriger</small></div>
              <div class="mh-key">4<small><i class="fas fa-arrow-left"></i></small></div><div class="mh-key">5</div><div class="mh-key">6<small><i class="fas fa-arrow-right"></i></small></div>
              <div class="mh-key plus">+<small>1 point</small></div>
              <div class="mh-key">1</div><div class="mh-key">2<small><i class="fas fa-arrow-down"></i></small></div><div class="mh-key">3</div>
              <div class="mh-key enter">⏎<small>fin de reprise</small></div>
              <div class="mh-key zero">0</div>
              <div class="mh-key pause">.<small>pause</small></div>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="mh-legend">
              <div class="sw" style="background:#16a34a">+</div><div><b>Un point</b>Ajoute 1 à la série en cours. La reprise se termine seule quand le joueur atteint ses points.</div>
              <div class="sw" style="background:#84252B">⏎</div><div><b>Fin de reprise</b>La série est ajoutée au score et la main passe à l'adversaire.</div>
              <div class="sw" style="background:#d97706">−</div><div><b>Corriger</b>Retire 1 point. Si la série est à 0, rouvre la reprise précédente pour la corriger.</div>
              <div class="sw" style="background:#2f4a7a">0-9</div><div><b>Série directe</b>Tapez la série (ex. <kbd>1</kbd><kbd>2</kbd>) puis <kbd>Entrée</kbd> : pratique pour une longue série. Dans les menus, les flèches de <kbd>8</kbd> <kbd>2</kbd> <kbd>4</kbd> <kbd>6</kbd> servent à se déplacer.</div>
              <div class="sw" style="background:#64748b">/</div><div><b>Effacer la saisie</b>Annule une série tapée par erreur, avant <kbd>Entrée</kbd>.</div>
              <div class="sw" style="background:#0284c7">*</div><div><b>Chrono</b>Lance ou met en pause le chrono par coup (et lance l'échauffement).</div>
              <div class="sw" style="background:#eab308;color:#0a1020">.</div><div><b>Pause</b>Affiche « Pause » en grand sur la TV (et en direct sur Internet) et arrête le chrono. Appuyez à nouveau sur <kbd>.</kbd> pour reprendre.</div>
              <div class="sw" style="background:#fde047;color:#0a1020"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5.5 2h13l-.5 6.3a6 6 0 0 1-11.9 0z"/><rect x="11" y="13.6" width="2" height="5.6"/><path d="M6.8 22c0-1.3 2.3-2.3 5.2-2.3s5.2 1 5.2 2.3z"/></svg></div><div><b>Appel du bar</b>Touche <kbd>Verr. Num</kbd>, puis <kbd>Entrée</kbd> pour confirmer (<kbd>⌫</kbd> pour renoncer). Le billard s'allume sur la tablette du bar et le barman vient prendre la commande.</div>
              <div class="sw" style="background:#0d9488">⌫</div><div><b>Menu</b>Corriger une reprise, modifier la partie en cours, plein écran, inverser les joueurs (à&nbsp;3&nbsp;: changer l'ordre de jeu), retour au billard, annuler la partie. Dans le menu : <kbd>8</kbd> <kbd>2</kbd> pour se déplacer, <kbd>Entrée</kbd> pour valider, <kbd>⌫</kbd> pour fermer.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Écran -->
    <div class="mh-section" id="ecran">
      <div class="mh-head"><div class="num"><i class="fas fa-tv"></i></div><div><h3>Lire l'écran du marquoir</h3><p>Ce que voient les joueurs et le public sur la TV</p></div></div>
      <div class="mh-body">
        <div class="mh-annot">
          <img class="mh-shot" src="<?= $img('06_marquoir') ?>" alt="Écran du marquoir annoté">
          <span class="mh-pin" style="left:20%;top:28%">1</span>
          <span class="mh-pin" style="left:37%;top:25%">2</span>
          <span class="mh-pin" style="left:31%;top:48%">3</span>
          <span class="mh-pin" style="left:11.5%;top:41.5%">4</span>
          <span class="mh-pin" style="left:57%;top:22%">5</span>
          <span class="mh-pin" style="left:57%;top:60%">6</span>
          <span class="mh-pin" style="left:18.6%;top:69%">7</span>
          <span class="mh-pin" style="left:24%;top:80.5%">8</span>
          <span class="mh-pin" style="left:87%;top:90.3%">9</span>
        </div>
        <div class="mh-pins-list">
          <div><span class="n">1</span><span><b>Joueur au billard</b> : son cadre s'allume, l'autre est grisé.</span></div>
          <div><span class="n">2</span><span><b>La bille</b> indique aussi le joueur qui joue (blanche ou jaune).</span></div>
          <div><span class="n">3</span><span><b>Score</b>, mis à jour à chaque fin de reprise.</span></div>
          <div><span class="n">4</span><span><b>5 dernières reprises</b> (n° et points), la plus récente en haut.</span></div>
          <div><span class="n">5</span><span><b>Reprise</b> en cours (et la limite éventuelle).</span></div>
          <div><span class="n">6</span><span><b>Série</b> en cours du joueur au billard.</span></div>
          <div><span class="n">7</span><span><b>Moyenne</b> et <b>meilleure série</b> du joueur.</span></div>
          <div><span class="n">8</span><span><b>Time-outs</b> : verts = disponibles, rouges = utilisés.</span></div>
          <div><span class="n">9</span><span><b>Chrono par coup</b> : la barre passe à l'orange puis au rouge.</span></div>
        </div>
      </div>
    </div>

    <!-- Chrono -->
    <div class="mh-section" id="chrono">
      <div class="mh-head"><div class="num"><i class="fas fa-stopwatch"></i></div><div><h3>Chrono par coup et time-outs</h3><p>Uniquement si l'option a été cochée à la création de la partie</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('10_timeout') ?>" alt="Time-out utilisé"><div class="mh-caption">Le temps est dépassé : un time-out est utilisé automatiquement et le chrono repart.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('11_temps_ecoule') ?>" alt="Temps écoulé"><div class="mh-caption">Plus de time-out : le chrono s'arrête et attend l'arbitre.</div></div>
        </div>
        <ol class="mh-steps">
          <li>Le chrono démarre avec <kbd>*</kbd> ou au premier point marqué <kbd>+</kbd>. Chaque point le relance à 40 secondes.</li>
          <li>Après <kbd>Entrée</kbd>, il revient à 40 s <b>à l'arrêt</b> : lancez-le avec <kbd>*</kbd> quand l'adversaire est prêt.</li>
          <li><b>Partie amicale</b> : si la case « Démarrage automatique après Entrée » a été cochée, le chrono repart tout seul à 40 s dès l'appui sur <kbd>Entrée</kbd> (<kbd>*</kbd> permet toujours de le mettre en pause). En rencontre officielle, c'est toujours l'arbitre qui le lance.</li>
          <li>Un seul bip, à <b>10 secondes</b> de la fin.</li>
          <li>Chaque dépassement consomme <b>un time-out</b> (l'icône devient rouge) et le chrono repart sans interrompre le joueur.</li>
          <li>Au <b>dernier</b> time-out, le chrono reste sur « Temps écoulé » : l'arbitre passe la main avec <kbd>Entrée</kbd>.</li>
          <li><b>Partie par sets</b> : le nombre de time-outs vaut <b>par set</b>. Ils sont tous rendus au début de chaque set.</li>
        </ol>
      </div>
    </div>

    <!-- Fin -->
    <div class="mh-section" id="fin">
      <div class="mh-head"><div class="num"><i class="fas fa-flag-checkered"></i></div><div><h3>Égalisation, fin de partie et corrections</h3><p>Le marquoir applique les règles tout seul</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('08_egalisation') ?>" alt="Reprise d'égalisation"><div class="mh-caption">Le premier joueur a terminé : l'adversaire joue sa reprise d'égalisation.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('09_resultat') ?>" alt="Résultat"><div class="mh-caption">Le résultat s'affiche à la fin de la partie.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mh-note"><b>Reprise d'égalisation</b> — quand le joueur qui a commencé atteint ses points, l'adversaire joue encore une reprise (sauf option « reprises inégales »). S'il termine aussi, c'est un match nul.</div>
            <div class="mh-note green"><b>Fin de partie</b> — <kbd>Entrée</kbd> ramène à la page du billard. Rencontre officielle : la feuille de match est enregistrée automatiquement pour le DS, et <kbd>+</kbd> permet de la signer (<a href="#signature">voir plus bas</a>). Les parties amicales n'ont pas de feuille de match.</div>
            <div class="mh-note green"><b>Revanche (partie amicale)</b> — <kbd>+</kbd> puis <kbd>Entrée</kbd> pour confirmer : nouvelle partie avec les mêmes joueurs et le même mode de jeu. On repasse par « Qui commence la partie ? » et l'échauffement. Pour d'autres joueurs ou un autre mode de jeu, recommencez une partie amicale depuis la page du billard.</div>
          </div>
          <div class="col-md-6">
            <div class="mh-note orange"><b>Une erreur ?</b> — <kbd>−</kbd> retire un point ; à 0, il rouvre la reprise précédente. Ça fonctionne même après la fin de la partie. Pour une reprise plus ancienne : <kbd>⌫</kbd> › <b>Corriger une reprise</b> (<a href="#corrections">voir plus bas</a>).</div>
            <div class="mh-note"><b>Abandonner</b> — <kbd>⌫</kbd> ouvre le menu : « Annuler la partie » (amicale) ou « Remettre à zéro » (rencontre officielle). Une confirmation est toujours demandée.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pause et corrections -->
    <div class="mh-section" id="corrections">
      <div class="mh-head"><div class="num"><i class="fas fa-pen-to-square"></i></div><div><h3>Pause, corrections et modifications</h3><p>Sans perdre une seule reprise</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('19_pause') ?>" alt="Écran Pause"><div class="mh-caption">Touche <kbd>.</kbd> : l'écran « Pause » reste affiché jusqu'au prochain appui.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('20_correction') ?>" alt="Corriger une reprise"><div class="mh-caption">« Corriger une reprise » : la série choisie est en cours de correction.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mh-note" style="border-left-color:#eab308"><b>Pause</b> — <kbd>.</kbd> affiche « Pause » sur la TV et chez ceux qui suivent en direct ; le chrono s'arrête. <kbd>.</kbd> à nouveau pour reprendre (le chrono se relance avec <kbd>*</kbd>).</div>
            <div class="mh-note green"><b>Modifier la partie en cours</b> — <kbd>⌫</kbd> › « Modifier la partie en cours » : nom mal tapé, mauvais points à réaliser, limite de reprises (et catégorie en rencontre officielle)… Les reprises jouées sont conservées.</div>
          </div>
          <div class="col-md-6">
            <ol class="mh-steps">
              <li><kbd>⌫</kbd> ouvre le menu, puis « <b>Corriger une reprise</b> » et <kbd>Entrée</kbd>.</li>
              <li><kbd>8</kbd> <kbd>2</kbd> choisissent la reprise, <kbd>4</kbd> <kbd>6</kbd> le joueur.</li>
              <li><kbd>Entrée</kbd>, tapez la bonne série, puis <kbd>Entrée</kbd> : tout est recalculé. <kbd>⌫</kbd> ferme l'écran.</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Signature de la feuille de match -->
    <div class="mh-section" id="signature">
      <div class="mh-head"><div class="num"><i class="fas fa-signature"></i></div><div><h3>Signer la feuille de match</h3><p>Rencontre officielle : les deux joueurs et l'arbitre signent sur le Chromebook</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('26_resultat_signer') ?>" alt="Partie terminée : signer la feuille de match"><div class="mh-caption">Partie terminée : <kbd>+</kbd> ou le bouton « Signer la feuille de match ».</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('27_signature') ?>" alt="Écran de signature"><div class="mh-caption">Un seul grand cadre ; à droite, qui signe. La coche verte : c'est signé.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li>Le <b>joueur A</b> signe du doigt dans le grand cadre blanc (un stylet capacitif est plus précis ; la main posée sur l'écran est ignorée).</li>
              <li>Touchez « <b>Joueur B</b> », puis « <b>Arbitre</b> » : chacun signe dans le même cadre. « Effacer » recommence la signature affichée.</li>
              <li>Tapez le <b>nom de l'arbitre</b> (obligatoire), puis « <b>Enregistrer</b> » : retour à la page du billard.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note green"><b>Facultatif</b> — <kbd>Entrée</kbd> ramène au billard sans signer. Les signatures sont imprimées sur la feuille de match PDF. Le DS choisit quelles rencontres sont à signer.</div>
            <div class="mh-note orange"><b>Définitif</b> — une fois enregistrée, la feuille est verrouillée : plus de correction ni de modification de la partie au marquoir.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Clôture d'une rencontre d'équipe -->
    <div class="mh-section" id="cloture">
      <div class="mh-head"><div class="num"><i class="fas fa-flag-checkered"></i></div><div><h3>Clôturer une rencontre d'équipe</h3><p>Coupe des Régions : signature des capitaines et feuille récapitulative</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('28_billard_rencontre') ?>" alt="Rencontre d'équipe du jour sur la page du billard"><div class="mh-caption">Sur chaque billard, le jour même : l'état des matchs de la rencontre.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('29_cloture') ?>" alt="Clôture de la rencontre"><div class="mh-caption">Le récapitulatif, le commentaire et la signature des deux capitaines.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li>Quand les matchs sont <b>terminés</b> (et leurs feuilles signées si c'est demandé), touchez « <b>Clôturer la rencontre</b> » sur la page d'un des billards.</li>
              <li>Vérifiez le récapitulatif ; ajoutez un <b>commentaire</b> si nécessaire.</li>
              <li>Chaque <b>capitaine</b> touche le bouton de son équipe et signe, puis « <b>Enregistrer les signatures</b> ».</li>
              <li>« <b>Feuille récapitulative PDF</b> » l'ouvre pour l'imprimer.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note green"><b>L'envoi par e-mail</b> (club adverse et responsable CDR) est fait par le <b>directeur sportif</b>, depuis l'administration.</div>
            <div class="mh-note orange"><b>Définitif</b> — une fois signée, la rencontre est verrouillée : plus de correction de ses matchs ni du commentaire au marquoir.</div>
            <div class="mh-note"><b>Le jour même uniquement</b> — le lendemain, le bloc disparaît des billards : tout se fait alors dans l'administration.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Moyenne proportionnelle -->
    <div class="mh-section" id="proportionnelle">
      <div class="mh-head"><div class="num"><i class="fas fa-balance-scale"></i></div><div><h3>Limite de reprises : la moyenne proportionnelle</h3><p>Rencontre officielle : quand personne n'a atteint ses points (ex. 60 reprises en 3 bandes)</p></div></div>
      <div class="mh-body">
        <div class="row g-4">
          <div class="col-lg-7"><img class="mh-shot" src="<?= $img('14_moyenne_prop') ?>" alt="Résultat départagé à la moyenne proportionnelle"><div class="mh-caption">MARTIN a moins de points et une moins bonne moyenne, mais gagne : il a mieux joué par rapport à sa catégorie.</div></div>
          <div class="col-lg-5">
            <div class="mh-formula">Moyenne proportionnelle =<br><b>moyenne réalisée ÷ moyenne minimum de la catégorie</b><small>La plus élevée l'emporte.</small></div>
            <div class="table-responsive">
              <table class="mh-ex">
                <tr><th>Joueur</th><th>Cat.</th><th>Moy.</th><th>Moy. min.</th><th>Prop.</th></tr>
                <tr><td>DUPONT</td><td>2 A</td><td>0,500</td><td>0,603</td><td>0,829</td></tr>
                <tr class="win"><td>MARTIN</td><td>3 C</td><td>0,300</td><td>0,358</td><td>0,838</td></tr>
              </table>
            </div>
            <div class="mh-note">Le marquoir fait le calcul tout seul et affiche <b>«&nbsp;Départagés à la moyenne proportionnelle&nbsp;»</b>.</div>
            <div class="mh-note orange">Il faut que la <b>catégorie des deux joueurs</b> soit encodée (par le DS). En <b>partie amicale</b>, pas de catégorie : le vainqueur est celui qui a réalisé le plus grand pourcentage de ses points.</div>
            <div class="mh-note green">Si un joueur atteint ses points, rien ne change : il gagne (ou match nul après la reprise d'égalisation).</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Par sets -->
    <div class="mh-section" id="sets">
      <div class="mh-head"><div class="num"><i class="fas fa-layer-group"></i></div><div><h3>Partie par sets (3 bandes)</h3><p>En amical comme en rencontre officielle</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-4">
          <div class="col-lg-7">
            <img class="mh-shot" src="<?= $img('15_sets_form') ?>" alt="Réglages d'une partie par sets">
            <div class="mh-caption">« Par sets » coché : reprises inégales imposées, plus de limite de reprises.</div>
          </div>
          <div class="col-lg-5">
            <ol class="mh-steps">
              <li>Choisissez la discipline <b>3 Bandes</b> : l'option <b>Par sets</b> apparaît.</li>
              <li>Cochez <b>Par sets</b>, puis indiquez :
                <ul>
                  <li>les <b>points par set</b> (15 proposé) ;</li>
                  <li>le nombre de <b>sets gagnants</b> (souvent 2 ou 3).</li>
                </ul>
              </li>
              <li><b>Reprises inégales</b> se coche toute seule : un set s'arrête dès qu'un joueur atteint ses points, sans reprise d'égalisation.</li>
              <li>Les points à réaliser de chaque joueur disparaissent : ce sont les <b>points par set</b> qui comptent.</li>
              <li><b>Chrono par coup</b> : le nombre de time-outs choisi vaut <b>par joueur et par set</b>.</li>
            </ol>
            <div class="mh-note orange mt-3"><b>Qui casse ?</b> — le joueur qui commence le 1<sup>er</sup> set garde la <b>bille blanche</b> toute la partie. Le joueur à la bille jaune casse le 2<sup>e</sup> set, et ainsi de suite, à tour de rôle.</div>
            <div class="mh-note green"><b>Exemple</b> — 2 sets gagnants de 15 points : le premier joueur à remporter 2 sets gagne la partie (2-0 ou 2-1).</div>
          </div>
        </div>
        <div class="row g-4 mb-3">
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('16_sets_marquoir') ?>" alt="Marquoir pendant un set"><div class="mh-caption">Pendant un set : le n° du set et le score en sets au centre.</div></div>
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('17_sets_pause') ?>" alt="Fin d'un set"><div class="mh-caption">Fin d'un set : le résumé et qui casse le set suivant.</div></div>
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('18_sets_resultat') ?>" alt="Résultat d'une partie par sets"><div class="mh-caption">Fin de partie : le détail de chaque set.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li>Le grand chiffre est le <b>score du set en cours</b>. Les pastilles sous le nom montrent les <b>sets gagnés</b>.</li>
              <li>La <b>moyenne</b> et la <b>série max</b> portent sur toute la partie ; les 5 dernières reprises, sur le set en cours.</li>
              <li>L'annonce <b>« Pour 3 … Pour 1 »</b> s'affiche dans chaque set.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note"><b>Fin d'un set</b> — l'écran affiche le résumé du set. <kbd>Entrée</kbd> lance le set suivant ; <kbd>−</kbd> corrige la dernière reprise si besoin.</div>
            <div class="mh-note green"><b>Fin de partie</b> — dès qu'un joueur a gagné le nombre de sets prévu : « Victoire de … 2 sets à 1 », avec les points (et les reprises) de chaque set.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Partie à 3 -->
    <div class="mh-section" id="trois">
      <div class="mh-head"><div class="num"><i class="fas fa-users"></i></div><div><h3>Partie amicale à 3 joueurs</h3><p>Trois joueurs, à tour de rôle (le dimanche matin, par exemple)</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-4">
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('21_amicale3') ?>" alt="Formulaire de partie amicale à 3"><div class="mh-caption">Le formulaire : nom, prénom et points de chacun des trois joueurs.</div></div>
          <div class="col-md-6"><img class="mh-shot" src="<?= $img('22_qui_commence3') ?>" alt="Choix de l'ordre de jeu"><div class="mh-caption">MARTIN commence ; il reste à choisir qui joue en 2<sup>e</sup>.</div></div>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li>Sur la page du billard, cliquez sur <b>Partie amicale à 3</b>.</li>
              <li>Pour chaque joueur : <b>nom</b>, <b>prénom</b> et <b>points</b> à réaliser (ceux du joueur 1 sont recopiés chez les deux autres).</li>
              <li>Discipline, format, <b>limite de reprises</b> et <b>reprises inégales</b> comme d'habitude. Pas de chrono ni de sets à 3.</li>
              <li><b>Continuer</b> : pas d'échauffement, on passe directement au choix de l'ordre de jeu.</li>
              <li><b>Qui commence ?</b> <kbd>1</kbd>, <kbd>2</kbd> ou <kbd>3</kbd>. Puis <b>Qui joue en 2<sup>e</sup> ?</b> Le 3<sup>e</sup> se déduit tout seul.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note orange"><b>Trompé ?</b> — avant le 2<sup>e</sup> choix, <kbd>−</kbd> recommence. Une fois la partie lancée : <kbd>⌫</kbd> › <b>« Changer l'ordre de jeu »</b>, tant qu'aucun coup n'a été joué.</div>
          </div>
        </div>
        <div class="row g-4 mb-3">
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('23_marquoir3') ?>" alt="Marquoir à 3 joueurs"><div class="mh-caption">Trois colonnes, reprises et série en bas. LECOMTE joue avec la blanche.</div></div>
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('24_egalisation3') ?>" alt="Fin du tour"><div class="mh-caption">MARTIN a fini : les deux autres terminent le tour.</div></div>
          <div class="col-md-4"><img class="mh-shot" src="<?= $img('25_resultat3') ?>" alt="Classement d'une partie à 3"><div class="mh-caption">Le classement : 1<sup>er</sup>, 2<sup>e</sup>, 3<sup>e</sup>.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li><kbd>Entrée</kbd> passe au joueur suivant : <b>1 → 2 → 3 → 1</b>. <kbd>−</kbd> revient au joueur précédent. Le reste du pavé fonctionne comme à deux.</li>
              <li><b>Deux billes pour trois</b> : elles alternent (A blanche, B jaune, C blanche, puis A jaune…). Les couleurs changent au début de chaque <b>tour</b>, après le 3<sup>e</sup> joueur.</li>
              <li>« Corriger une reprise » : <kbd>4</kbd> <kbd>6</kbd> passent d'un joueur à l'autre parmi les trois.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note"><b>Fin du tour</b> — quand un joueur atteint ses points, les suivants jouent encore leur reprise : la partie s'arrête à la fin du tour. Avec « reprises inégales », elle s'arrête tout de suite.</div>
            <div class="mh-note green"><b>Classement</b> — au pourcentage des points réalisés (ex æquo possibles). <kbd>+</kbd> rejoue la même partie à trois.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- En direct -->
    <div class="mh-section" id="direct">
      <div class="mh-head"><div class="num"><i class="fas fa-broadcast-tower"></i></div><div><h3>Suivre une rencontre en direct, de chez soi</h3><p>Sur ordinateur, tablette ou smartphone</p></div></div>
      <div class="mh-body">
        <div class="row g-4 mb-3 align-items-start">
          <div class="col-md-5"><img class="mh-shot" src="<?= $img('13_direct_liste_crop') ?>" alt="Rencontres du jour en direct"><div class="mh-caption">Les rencontres officielles du jour, avec le score en cours.</div></div>
          <div class="col-md-7"><img class="mh-phone" src="<?= $img('12_direct_telephone') ?>" alt="Marquoir en direct sur smartphone"><div class="mh-caption">Le marquoir, en direct, sur un smartphone tenu en paysage.</div></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <ol class="mh-steps">
              <li>Menu <b>Saison › En direct</b> <i class="fas fa-tv" style="color:#84252B"></i> du site.</li>
              <li>Cliquez sur <b>Visionner</b> à côté de la rencontre qui vous intéresse.</li>
              <li>L'écran se met à jour tout seul, <b>toutes les 2 secondes</b>.</li>
            </ol>
          </div>
          <div class="col-md-6">
            <div class="mh-note">Seules les <b>rencontres officielles</b> sont diffusées : les parties amicales restent privées.</div>
            <div class="mh-note green">Sur smartphone, tournez l'appareil en <b>mode paysage</b> pour retrouver l'écran du club.</div>
          </div>
        </div>
        <p class="text-center mt-4 mb-0">
          <a href="<?= base_url('en-direct') ?>" class="btn mh-pdf"><i class="fas fa-tv me-2"></i>Voir les rencontres en direct</a>
          <span class="d-block mt-3" style="color:#5d677c">Une question ? <a href="mailto:ds@rbcd.be">ds@rbcd.be</a></span>
        </p>
      </div>
    </div>

  </div>
</section>
<?= $this->endSection() ?>
