<dialog class="modal" id="confirm-dialog" aria-labelledby="confirm-title">
    <form method="dialog" class="modal__box">
        <h2 class="modal__title" id="confirm-title">Confirmar</h2>
        <p class="modal__text" data-confirm-text></p>
        <div class="modal__actions">
            <button class="btn btn--ghost" value="cancel" type="submit">Cancelar</button>
            <button class="btn btn--danger-solid" value="ok" type="submit" data-confirm-ok>Sí, continuar</button>
        </div>
    </form>
</dialog>
