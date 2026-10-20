<?php
use App\Filters\ScoreboardDeviceFilter as D;

$asset = static fn (string $path): string => base_url($path) . '?v=' . @filemtime(FCPATH . $path);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Caisse — Bar | RBC Disonais</title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicons/favicon.ico') ?>">
    <meta name="theme-color" content="#0b1020">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $asset('assets/cash-register/caisse.css') ?>">
</head>
<body>
<div class="app">
    <header class="hd">
        <div class="logo"><img src="<?= base_url('assets/images/favicons/apple-touch-icon.png') ?>" alt="RBCD"></div>
        <div class="brand"><strong>Caisse — Bar</strong><small>Royal Billard Club Disonais</small></div>
        <div class="grow"></div>
        <span class="meta" id="clock"></span>
        <span class="svc" id="hdSvc"></span>
        <span class="pill" id="net"><i></i><span>Connectée</span></span>
    </header>

    <div class="main" id="main">
        <!-- clavier : nom du client ou du barman -->
        <section class="kb" id="kb" hidden>
            <div class="kb-title"><strong id="kbTitle">Nom du client</strong><small>Majuscules uniquement · la recherche ignore les accents</small></div>
            <div class="keys" id="keys"></div>
        </section>

        <!-- ouverture du service -->
        <aside class="side open" id="sideOpen" hidden>
            <div class="nlabel">Ouverture du service</div>
            <div class="svc-card">
                <div class="d" id="openDate"></div>
                <div class="seg" id="seg">
                    <button class="btn" type="button" data-p="am">Après-midi</button>
                    <button class="btn" type="button" data-p="soir">Soir</button>
                </div>
                <div class="env"><span>Enveloppe</span><b id="envCode"></b></div>
            </div>
            <div class="okline" id="lastClosed" hidden><i></i><span></span></div>
            <div class="nlabel">1 · Qui ouvre ? · ton nom</div>
            <div class="namebox small" id="bmBox"></div>
            <div class="found" id="bmFound"></div>
            <div class="sugg" id="bmSugg"></div>
            <div class="nlabel" id="fundLabel"></div>
            <div class="namebox small" id="fundBox"></div>
            <div class="found" id="fundFound"></div>
            <button class="btn primary big" type="button" id="btnOpen" disabled>Ouvrir le service</button>
        </aside>

        <!-- clôture : qui clôture -->
        <aside class="side open" id="sideCloser" hidden>
            <div class="nlabel">Clôture du service</div>
            <div class="svc-card">
                <div class="d" id="clSvc"></div>
                <div class="env"><span>Enveloppe</span><b id="clEnv"></b></div>
            </div>
            <div class="nlabel">1 · Qui clôture ? · ton nom</div>
            <div class="namebox small" id="clBox"></div>
            <div class="found" id="clFound"></div>
            <div class="sugg" id="clSugg"></div>
            <div class="nlabel" id="clFundLabel"></div>
            <div class="namebox small" id="clFundBox"></div>
            <div class="found" id="clFundFound"></div>
            <button class="btn orange big" type="button" id="btnCloseGo" disabled><span><i class="fas fa-key"></i> Clôturer le service</span></button>
            <button class="btn" type="button" data-go="close">Retour au résumé</button>
        </aside>

        <!-- caisse -->
        <section class="left" id="left" hidden>
            <div class="cats" id="cats"></div>
            <div class="grid" id="grid"></div>
        </section>
        <aside class="side ticket" id="sideTicket" hidden>
            <div class="row2">
                <button class="btn primary" type="button" data-go="new">Créer un nouveau ticket</button>
                <button class="btn" type="button" data-go="list" id="btnList">Tickets en cours <span class="cnt" data-count></span></button>
            </div>
            <div class="tk" id="tk"></div>
            <div class="lbl"><span>Consommations</span><span id="count"></span></div>
            <div class="lines" id="lines"></div>
            <div class="total"><span>Total</span><strong id="total">0,00 €</strong></div>
            <div class="next" id="nextHint">Le client reprendra quelque chose plus tard&nbsp;? Touche <b>«&nbsp;Tickets en cours&nbsp;»</b>&nbsp;: son ticket reste ouvert et la caisse est prête pour le suivant.</div>
            <div class="act">
                <button class="btn danger validate" type="button" id="btnCancel">Annuler</button>
                <button class="btn primary validate" type="button" id="btnValidate">Payer</button>
            </div>
        </aside>

        <!-- nouveau ticket -->
        <aside class="side new" id="sideNew" hidden>
            <div class="row2">
                <button class="btn dark" type="button">Créer un nouveau ticket</button>
                <button class="btn" type="button" data-go="list">Tickets en cours <span class="cnt" data-count></span></button>
            </div>
            <div class="nlabel" id="newLabel"></div>
            <div class="namebox" id="nameBox"></div>
            <div class="found" id="found"></div>
            <div class="sugg" id="sugg"></div>
            <div class="help">Touche un client pour ouvrir son ticket,<br>ou crée-le s'il n'existe pas encore.</div>
        </aside>

        <!-- tickets en cours -->
        <section class="full" id="list" hidden>
            <div class="full-hd">
                <div><h2>Tickets en cours</h2><p id="listSub"></p></div>
                <div class="btns">
                    <button class="btn primary" type="button" data-go="new">Créer un nouveau ticket</button>
                    <button class="btn" type="button" data-go="ticket">Retour à la caisse</button>
                </div>
            </div>
            <div class="scroll" id="cardsWrap"><div class="cards" id="cards"></div></div>
            <div class="note" id="listNote"><i></i><span>Chaque ticket ouvert doit être réglé ou annulé avant la clôture du service.</span></div>
            <div class="foot">
                <button class="ghost" type="button" data-go="history"><i class="far fa-list-alt"></i>Historique du service</button>
                <button class="ghost" type="button" data-go="close" id="btnGoClose" title="Tous les tickets ouverts doivent être réglés ou annulés"><i class="fas fa-key"></i>Clôturer le service</button>
            </div>
        </section>

        <!-- historique du service -->
        <section class="full" id="history" hidden>
            <div class="full-hd">
                <div><h2>Historique du service</h2><p id="histSub"></p></div>
                <div class="btns"><button class="btn" type="button" data-go="list">Retour aux tickets en cours</button></div>
            </div>
            <div class="scroll" id="histWrap"></div>
            <div class="note ok"><i></i>Tickets réglés et annulés de ce service, du plus récent au plus ancien.</div>
        </section>

        <!-- clôture -->
        <section class="close-wrap" id="close" hidden>
            <div class="close-sum">
                <div class="label">Résumé du service</div>
                <div class="tiles3">
                    <div class="tile"><span>Tickets validés</span><b id="cPaid">0</b></div>
                    <div class="tile"><span>Tickets annulés</span><b id="cCancelled">0</b></div>
                    <div class="tile hl"><span>Total des ventes</span><b id="cTotal">0,00 €</b></div>
                </div>
                <div>
                    <div class="label" style="margin-bottom:.3rem">Par mode de paiement</div>
                    <div class="pm-row"><div><b>Espèces</b><small id="cCashN"></small></div><span class="v" id="cCash"></span></div>
                    <div class="pm-row"><div><b>Bancontact (SumUp)</b><small id="cBcN"></small></div><span class="v" id="cBc"></span></div>
                    <div class="pm-row"><div><b>TVA 21 % B (bar) · 21 % G (gougouilles)</b><small>Pour l'encodage de l'enveloppe</small></div><span class="v" id="cVat" style="font-size:1.05rem"></span></div>
                </div>
                <div></div>
                <div class="note" id="cNote"><i></i><span></span></div>
            </div>
            <div class="close-env">
                <div class="label">Enveloppe de ce service</div>
                <div class="code" id="cEnv"></div>
                <div class="sub" id="cWhen"></div>
                <div class="sub" id="cRange"></div>
                <div style="display:grid;align-content:center;gap:.3rem">
                    <div class="sub">Espèces attendues dans l'enveloppe</div>
                    <div class="cash" id="cExpected"></div>
                    <div class="sub" id="cHint">Compte le tiroir, glisse l'argent et le récapitulatif dans l'enveloppe, puis note son numéro dessus.</div>
                </div>
                <div class="warn" id="cWarn">Après la clôture, plus aucun ticket ne peut être ajouté, modifié ou supprimé pour ce service.</div>
                <button class="btn orange" type="button" id="btnClose"><i class="fas fa-key"></i>Clôturer le service</button>
                <button class="btn outline" type="button" id="btnRecap" hidden>Imprimer le récapitulatif</button>
                <button class="btn link" type="button" id="btnCloseBack">Retour aux tickets en cours</button>
            </div>
        </section>
    </div>

    <footer class="band" id="band"></footer>
</div>

<div class="modal" id="modal" hidden>
    <div class="modal-box">
        <h3 id="mTitle"></h3>
        <p id="mText"></p>
        <div class="reasons" id="mReasons" hidden>
            <button class="btn" type="button">Erreur de saisie</button>
            <button class="btn" type="button">Client parti</button>
            <button class="btn" type="button">Ticket en double</button>
            <button class="btn" type="button">Autre raison</button>
        </div>
        <div class="acts">
            <button class="btn" type="button" id="mNo">Non, revenir</button>
            <button class="btn confirm-danger" type="button" id="mYes">Oui</button>
        </div>
    </div>
</div>

<div class="modal" id="payModal" hidden>
    <div class="modal-box">
        <h3 id="pTitle"></h3>
        <p id="pText"></p>
        <div class="paybig">
            <button class="btn" type="button" data-m="cash">Espèces</button>
            <button class="btn" type="button" data-m="bancontact">Bancontact<small>encaisser d'abord sur SumUp</small></button>
        </div>
        <button class="btn" type="button" id="pNo">Annuler</button>
    </div>
</div>

<div class="toast" id="toast" hidden><span id="toastText"></span><button class="btn" type="button" id="toastAction" hidden></button></div>

<div class="offline" id="offline" hidden>
    <div class="offline-box">
        <div class="offline-icon">!</div>
        <h2>Pas de connexion Internet</h2>
        <p class="lead">La caisse est bloquée tant que le Wi-Fi est coupé.</p>
        <ol>
            <li><b>Notez les consommations sur papier</b> : nom du client, produits, mode de paiement.</li>
            <li>Vérifiez le Wi-Fi de la tablette.</li>
            <li>La caisse se débloque <b>toute seule</b> au retour de la connexion : encodez alors ce qui a été noté.</li>
        </ol>
        <p class="since" id="offlineSince"></p>
    </div>
</div>

<div class="unlock" id="unlock">
    <div>
        <div class="big">Touchez l'écran pour démarrer la caisse</div>
        <p>La tablette passe en plein écran et pourra sonner à chaque appel d'un billard.</p>
    </div>
</div>

<?php if (D::isCommittee() && !D::device()): ?>
<form class="device" method="post" action="<?= base_url('marquoir/appareil/autoriser') ?>" id="deviceForm">
    <?= csrf_field() ?>
    <input type="hidden" name="back" value="<?= esc(base_url('caisse')) ?>">
    <span><b>Appareil non autorisé.</b> Vous y avez accès parce que vous êtes connecté (comité).</span>
    <input type="text" name="name" maxlength="60" required value="Tablette du bar">
    <button class="btn primary" type="submit">Autoriser cet appareil</button>
</form>
<?php endif; ?>

<script>window.CAISSE = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= $asset('assets/cash-register/caisse.js') ?>"></script>
</body>
</html>
