( function () {
    const settings = window.wc.wcSettings.getSetting( 'boltutil_usdt_data', {} );
    const el = window.wp.element.createElement;
    const useState = window.wp.element.useState;
    const useEffect = window.wp.element.useEffect;
    const decode = window.wp.htmlEntities.decodeEntities;
    const networks = settings.networks || {};
    const codes = Object.keys( networks );

    function Content( props ) {
        const [ network, setNetwork ] = useState( codes[ 0 ] || '' );
        const onPaymentSetup = props.eventRegistration && props.eventRegistration.onPaymentSetup;
        useEffect( () => {
            if ( ! onPaymentSetup ) {
                return undefined;
            }
            return onPaymentSetup( () => {
                if ( ! network || ! Object.prototype.hasOwnProperty.call( networks, network ) ) {
                    return { type: props.emitResponse.responseTypes.ERROR, message: settings.networkError || 'Choose an available USDT network.' };
                }
                return {
                    type: props.emitResponse.responseTypes.SUCCESS,
                    meta: { paymentMethodData: { boltutil_route: network } },
                };
            } );
        }, [ network, onPaymentSetup ] );

        return el( 'div', { className: 'boltutil-payment-panel' },
            el( 'p', { className: 'boltutil-payment-description' }, decode( settings.description || '' ) ),
            el( 'fieldset', { className: 'boltutil-network-fieldset' },
                el( 'legend', null, settings.networkLabel || 'Choose a USDT payment network' ),
                el( 'div', { className: 'boltutil-network-list' }, codes.map( ( code ) => {
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
            decode( settings.title || 'USDT via BoltUtil' ) ),
        content: el( Content ),
        edit: el( Content ),
        canMakePayment: () => codes.length > 0,
        ariaLabel: decode( settings.title || 'USDT via BoltUtil' ),
        supports: { features: settings.supports || [ 'products' ] },
    } );
} )();
