<?php layout_start('public', ['title' => $title]); ?>
<section class="page-hero">
    <div class="container">
        <h1 class="h2 mb-0"><?= e($title) ?></h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="card-soft">
            <?= $content ?>
        </div>
    </div>
</section>
<?php layout_end('public'); ?>
