( function () {
    const model = window.BoltUtilCheckoutRoutes;
    let lastChoice = null;

    function initialize( panel ) {
        if ( panel.dataset.boltutilInitialized ) return;
        panel.dataset.boltutilInitialized = 'true';
        const coins = Array.from( panel.querySelectorAll( 'input[name="boltutil_token"]' ) );
        const inputs = Array.from( panel.querySelectorAll( 'input[name="boltutil_route"]' ) );
        if ( ! coins.length || ! inputs.length ) return;
        const routes = {};
        inputs.forEach( ( input ) => {
            routes[ input.value ] = { token: input.dataset.token, network: input.dataset.network };
        } );
        const selectCoin = ( token, previous ) => {
            const selected = model.select( routes, token, previous );
            coins.forEach( ( input ) => { input.checked = input.value === token; } );
            inputs.forEach( ( input ) => {
                const visible = input.dataset.token === token;
                input.disabled = ! visible;
                input.checked = input.value === selected;
                input.closest( '.boltutil-network-option' ).hidden = ! visible;
            } );
            lastChoice = { token, route: selected };
        };
        const token = lastChoice && model.tokens( routes ).includes( lastChoice.token )
            ? lastChoice.token : coins[ 0 ].value;
        selectCoin( token, lastChoice && lastChoice.route );
        panel.querySelector( '.boltutil-token-fieldset' ).hidden = false;
        panel.addEventListener( 'change', ( event ) => {
            if ( event.target.name === 'boltutil_token' ) selectCoin( event.target.value, lastChoice && lastChoice.route );
            if ( event.target.name === 'boltutil_route' && routes[ event.target.value ] ) {
                lastChoice = { token: routes[ event.target.value ].token, route: event.target.value };
            }
        } );
    }

    const scan = () => document.querySelectorAll( '.boltutil-classic-panel' ).forEach( initialize );
    if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', scan );
    else scan();
    // Classic checkout replaces payment fields when shipping/billing changes.
    new MutationObserver( scan ).observe( document.body, { childList: true, subtree: true } );
} )();
