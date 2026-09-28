<?php
/** Regression check: a running reconciliation must queue its next poll. */
define( 'ABSPATH', __DIR__ );

class WC_Payment_Gateway {}

$GLOBALS['boltutil_pending_actions'] = array();
$GLOBALS['boltutil_scheduled_actions'] = array();

function as_get_scheduled_actions( $query, $format ) {
    if ( 'ids' !== $format || 'boltutil_wc_reconcile_order' !== $query['hook'] ||
        'boltutil' !== $query['group'] || 'pending' !== $query['status'] ||
        array( 42 ) !== $query['args'] || 1 !== $query['per_page'] ) {
        throw new RuntimeException( 'Reconciliation must check only pending actions for this order.' );
    }
    return $GLOBALS['boltutil_pending_actions'];
}

function as_schedule_single_action( $when, $hook, $args, $group ) {
    $GLOBALS['boltutil_scheduled_actions'][] = compact( 'when', 'hook', 'args', 'group' );
}

// Action Scheduler reports an in-progress action as scheduled through this
// older API. If production code calls it, this fixture catches the regression.
function as_next_scheduled_action() {
    throw new RuntimeException( 'An in-progress action must not block its next poll.' );
}

require __DIR__ . '/../includes/class-boltutil-wc-gateway.php';

$method = new ReflectionMethod( 'BoltUtil_WC_Gateway', 'schedule_reconcile' );
$method->setAccessible( true );
$before = time();
$method->invoke( null, 42, 300 );
$scheduled = $GLOBALS['boltutil_scheduled_actions'];
if ( 1 !== count( $scheduled ) ||
    'boltutil_wc_reconcile_order' !== $scheduled[0]['hook'] ||
    array( 42 ) !== $scheduled[0]['args'] ||
    'boltutil' !== $scheduled[0]['group'] ||
    $scheduled[0]['when'] < $before + 300 ) {
    throw new RuntimeException( 'An in-progress reconciliation did not queue its next poll.' );
}

$GLOBALS['boltutil_pending_actions'] = array( 123 );
$method->invoke( null, 42, 300 );
if ( 1 !== count( $GLOBALS['boltutil_scheduled_actions'] ) ) {
    throw new RuntimeException( 'An existing pending reconciliation was duplicated.' );
}

echo "Reconciliation scheduling fixtures: OK\n";
