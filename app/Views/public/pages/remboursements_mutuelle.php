<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Remboursements Mutuelle</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Votre mutuelle peut rembourser une partie de votre affiliation à un club sportif reconnu.
            Téléchargez le formulaire correspondant à votre mutuelle, complétez-le et renvoyez-le à votre organisme.
          </p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12 text-center">
        <h4 class="font-weight-700 mt-0 mb-10">Cliquez sur le logo de votre mutuelle pour télécharger le document à remplir :</h4>
        <div class="mutuelles-logos mt-30">
          <a href="<?= base_url('uploads/mutuelles/MC-ClubSportif.pdf') ?>" title="Mutuelle Chrétienne" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Chretienne.jpg') ?>" alt="Mutuelle Chrétienne">
            <p class="mutuelle-name">Mutuelle Chrétienne</p>
          </a>
          <a href="<?= base_url('uploads/mutuelles/SOLIDARIS-ClubSportif.pdf') ?>" title="Solidaris — Mutuelle Socialiste" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Socialiste.jpg') ?>" alt="Solidaris">
            <p class="mutuelle-name">Solidaris</p>
          </a>
          <a href="<?= base_url('uploads/mutuelles/MUTUALIA-ClubSportif.pdf') ?>" title="Mutuelle Neutre" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Neutre.jpg') ?>" alt="Mutuelle Neutre">
            <p class="mutuelle-name">Mutuelle Neutre</p>
          </a>
          <a href="<?= base_url('uploads/mutuelles/MUTUALITE-LIBERALE-ClubSportif.pdf') ?>" title="Mutualité Libérale" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Liberale.jpg') ?>" alt="Mutualité Libérale">
            <p class="mutuelle-name">Mutualité Libérale</p>
          </a>
          <a href="<?= base_url('uploads/mutuelles/PARTENAMUT-ClubSportif.pdf') ?>" title="Partenamut" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Partenamut.jpg') ?>" alt="Partenamut">
            <p class="mutuelle-name">Partenamut</p>
          </a>
        </div>

        <div class="mutuelle-info mt-40">
          <i class="fas fa-info-circle fa-lg"></i>
          <span>Si votre mutuelle ne figure pas dans cette liste, renseignez-vous directement auprès d'elle —
          de nombreuses mutuelles remboursent l'affiliation sportive sur présentation d'une attestation du club.</span>
        </div>
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.mutuelles-logos {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 40px;
}
.mutuelle-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
}
.mutuelle-link img {
    max-height: 90px;
    max-width: 200px;
    object-fit: contain;
    filter: grayscale(20%);
    opacity: .85;
    transition: opacity .2s, filter .2s, transform .2s;
}
.mutuelle-link:hover img {
    opacity: 1;
    filter: grayscale(0%);
    transform: scale(1.05);
}
.mutuelle-name {
    margin-top: 10px;
    font-size: .85rem;
    color: #555;
    font-weight: 600;
}
.mutuelle-info {
    display: inline-flex;
    align-items: flex-start;
    gap: 12px;
    background: #f8f9fa;
    border-left: 4px solid #84252B;
    padding: 16px 20px;
    border-radius: 4px;
    font-size: .9rem;
    color: #555;
    text-align: left;
    max-width: 680px;
    transition: box-shadow .2s;
}
.mutuelle-info:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
.mutuelle-info i { flex-shrink: 0; margin-top: 2px; }
.mutuelle-info a { color: #84252B; }
</style>
<?= $this->endSection() ?>
