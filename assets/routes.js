( function () {
    // UI choices always come from merchant-scoped server capabilities.
    const tokens = ( routes ) => [ 'USDT', 'USDC' ].filter( ( token ) =>
        Object.values( routes ).some( ( route ) => route.token === token ) );
    const forToken = ( routes, token ) => Object.keys( routes ).filter( ( id ) => routes[ id ].token === token );
    const select = ( routes, token, previous ) => {
        const eligible = forToken( routes, token );
        const previousNetwork = routes[ previous ] && routes[ previous ].network;
        return eligible.find( ( id ) => routes[ id ].network === previousNetwork ) || eligible[ 0 ] || '';
    };
    window.BoltUtilCheckoutRoutes = { tokens, forToken, select };
} )();
