<?= $this->extend('layout/main') ?>

<?= $this->section('head') ?>
<!-- Можно подключить кастомные стили здесь -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <h2>Welcome!</h2>
  <p>We’re building a service to help you find authentic Georgian restaurants near you.</p>
  <p>Stay tuned!</p>
<?= $this->endSection() ?>
