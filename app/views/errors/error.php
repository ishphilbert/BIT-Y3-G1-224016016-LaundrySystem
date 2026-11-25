<section class="container narrow error-page">
    <h1><?= (int) $code ?></h1>
    <p class="lead"><?= e($message) ?></p>
    <p><a class="btn btn-primary" href="<?= e(url('index.php')) ?>">Back to home</a></p>
</section>
