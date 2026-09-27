( function () {
    const button = document.querySelector( '.boltutil-copy-webhook' );
    const url = document.getElementById( 'boltutil-webhook-url' );
    if ( ! button || ! url ) {
        return;
    }
    button.addEventListener( 'click', async function () {
        try {
            await navigator.clipboard.writeText( url.textContent.trim() );
            button.textContent = button.dataset.copiedLabel || 'Copied';
        } catch ( error ) {
            // Selectable text remains visible when clipboard access is unavailable.
            button.focus();
        }
    } );
} )();
