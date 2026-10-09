( function () {
    const settings = window.wc.wcSettings.getSetting( 'boltutil_usdt_data', {} );
    const el = window.wp.element.createElement;
    const useState = window.wp.element.useState;
    const useEffect = window.wp.element.useEffect;
    const decode = window.wp.htmlEntities.decodeEntities;
    const networks = settings.networks || {};
    const codes = Object.keys( networks );
    const routes = window.BoltUtilCheckoutRoutes;
    const tokens = routes.tokens( networks );

    function Content( props ) {
        const [ token, setToken ] = useState( tokens[ 0 ] || '' );
        const [ network, setNetwork ] = useState( routes.select( networks, tokens[ 0 ] ) );
        const available = routes.forToken( networks, token );
        const chooseToken = ( next ) => {
            setToken( next );
            setNetwork( routes.select( networks, next, network ) );
        };
        const onPaymentSetup = props.eventRegistration && props.eventRegistration.onPaymentSetup;
        useEffect( () => {
            if ( ! onPaymentSetup ) {
                return undefined;
            }
            return onPaymentSetup( () => {
                if ( ! network || ! Object.prototype.hasOwnProperty.call( networks, network ) || networks[ network ].token !== token ) {
                    return { type: props.emitResponse.responseTypes.ERROR, message: settings.networkError || 'Choose an available stablecoin and network.' };
                }
                return {
                    type: props.emitResponse.responseTypes.SUCCESS,
                    meta: { paymentMethodData: { boltutil_route: network } },
                };
            } );
        }, [ network, token, onPaymentSetup ] );

        return el( 'div', { className: 'boltutil-payment-panel' },
            el( 'p', { className: 'boltutil-payment-description' }, decode( settings.description || '' ) ),
            el( 'fieldset', { className: 'boltutil-token-fieldset' },
                el( 'legend', null, settings.tokenLabel || 'Choose a stablecoin' ),
                el( 'div', { className: 'boltutil-token-list' }, tokens.map( ( coin ) =>
                    el( 'label', { className: 'boltutil-token-option', key: coin },
                        el( 'input', { type: 'radio', name: 'boltutil-block-token', value: coin, checked: token === coin,
                            onChange: () => chooseToken( coin ) } ),
                        el( 'img', { src: ( settings.tokenIcons || {} )[ coin ] || '', alt: '', 'aria-hidden': true } ),
                        el( 'strong', null, coin ) ) ) ) ),
            el( 'fieldset', { className: 'boltutil-network-fieldset' },
                el( 'legend', null, settings.networkLabel || 'Choose a payment network' ),
                el( 'div', { className: 'boltutil-network-list' }, available.map( ( code ) => {
                    const item = networks[ code ];
                    return el( 'label', { className: 'boltutil-network-option', key: code },
                        el( 'input', { type: 'radio', name: 'boltutil-block-network', value: code, checked: network === code,
                            onChange: () => setNetwork( code ) } ),
                        el( 'span', { className: 'boltutil-network-art', 'aria-hidden': true },
                            el( 'span', { className: 'boltutil-chain-badge network-' + item.network.toLowerCase() },
                                el( 'img', { src: item.icon || '', alt: '', loading: 'lazy' } ) ),
                            el( 'span', { className: 'boltutil-token-badge' },
                                el( 'img', { src: item.tokenIcon || settings.tokenIcon || '', alt: '', loading: 'lazy' } ) ) ),
                        el( 'span', { className: 'boltutil-network-copy' },
                            el( 'strong', null, item.label ), el( 'small', null, item.short ) ),
                        el( 'span', { className: 'boltutil-network-arrow', 'aria-hidden': true }, '→' ),
                        el( 'span', { className: 'boltutil-network-selected', 'aria-hidden': true },
                            '✓ ', settings.selectedLabel || 'Selected' )
                    );
                } ) ) ),
            el( 'p', { className: 'boltutil-payment-note' }, settings.redirectNotice || '' )
        );
    }

    window.wc.wcBlocksRegistry.registerPaymentMethod( {
        name: 'boltutil_usdt',
        paymentMethodId: 'boltutil_usdt',
        label: el( 'span', { className: 'boltutil-payment-label' },
            el( 'img', { src: settings.icon || '', alt: '', 'aria-hidden': true } ),
            decode( settings.title || 'Pay with BoltUtil' ) ),
        content: el( Content ),
        edit: el( Content ),
        canMakePayment: () => codes.length > 0,
        ariaLabel: decode( settings.title || 'Pay with BoltUtil' ),
        supports: { features: settings.supports || [ 'products' ] },
    } );
} )();
