<?php

namespace WeDevs\DokanMigrator\Processors;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WeDevs\DokanMigrator\Abstracts\Processor;
use WeDevs\DokanMigrator\Integrations\Wcfm\WithdrawMigrator as WcfmWithdrawMigrator;

/**
 * Withdraw migration handler class.
 *
 * @since 1.0.0
 */
class Withdraw extends Processor {

    /**
     * Returns count of items vendor.
     *
     * @since 1.0.0
     *
     * @param string $plugin
     *
     * @return integer
     */
    public static function get_total( $plugin ) {
        global $wpdb;

        switch ( $plugin ) {
            case 'wcfmmarketplace':
                return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request WHERE withdraw_status!='requested'" );

            default:
                return 0;
        }
    }

    /**
     * Returns array of items vendor.
     *
     * @since 1.0.0
     *
     * @return array
     * @throws \Exception
     */
    public static function get_items( $plugin, $number, $offset ) {
        global $wpdb;
        $withdraws = [];

        if ( 0 === (int) $offset ) {
            self::remove_existing_withdraw_data();
        }

        switch ( $plugin ) {
            case 'wcfmmarketplace':
                $withdraws = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT *
                        FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
                        WHERE withdraw_status!='requested'
                        ORDER BY ID
                        LIMIT %d
                        OFFSET %d",
                        $number,
                        $offset
                    )
                );
        }

        if ( empty( $withdraws ) ) {
            self::throw_error();
        }

        return $withdraws;
    }

    /**
     * Return class to handle migration.
     *
     * @since 1.0.0
     *
     * @param string $plugin
     * @param object $payload
     *
     * @return object
     * @throws \Exception
     */
    public static function get_migration_class( $plugin, $payload ) {
        switch ( $plugin ) {
            case 'wcfmmarketplace':
                return new WcfmWithdrawMigrator( $payload );
        }

        throw new \Exception( __( 'Migrator class not found', 'dokan-migrator' ) );
    }

    /**
     * Removes old withdraw data from table.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function remove_existing_withdraw_data() {
        global $wpdb;

        $wpdb->query( "DELETE FROM {$wpdb->prefix}dokan_withdraw WHERE 1" );
    }

    /**
     * Throws error on empty data or unsupported plugin.
     *
     * @since 1.0.0
     *
     * @return void
     * @throws \Exception
     */
    public static function throw_error() {
        delete_option( 'dokan_migrator_last_migrated' );
        update_option( 'dokan_migration_completed', 'yes' );
        update_option( 'dokan_migration_success', 'yes' );
        throw new \Exception( __( 'No withdraws found to migrate to dokan.', 'dokan-migrator' ) );
    }
}
