<?php foreach ($flash ?? [] as $msg): ?>
<div class="alert alert--<?= e($msg['type']) ?>" role="status">
    <?= e($msg['message']) ?>
    <button class="alert__close" type="button" data-dismiss aria-label="Cerrar aviso">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>
</div>
<?php endforeach; ?>
