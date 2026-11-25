<?php if (!empty($errors)): ?>
    <div class="alert alert-error" role="alert">
        <ul>
            <?php foreach ($errors as $msg): ?>
                <li><?= e($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
