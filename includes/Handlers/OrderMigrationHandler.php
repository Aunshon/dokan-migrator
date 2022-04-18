<?php

namespace Wedevs\DokanMigrator\Handlers;

use Wedevs\DokanMigrator\Abstracts\Handler;
use Wedevs\DokanMigrator\Integrations\Wcfm\OrderMigrator as WcfmOrderMigrator;
use Wedevs\DokanMigrator\Integrations\WcVendors\OrderMigrator as WcVendorsOrderMigrator;

class OrderMigrationHandler extends Handler {

    /**
     * Returns count of items vendor.
     *
     * @since 1.0.0
     *
     * @param string $plugin
     *
     * @return integer
     */
    public function get_total( $plugin ) {
        global $wpdb;

        $total_count = 0;
        $sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type='shop_order' AND post_parent=0" );

        $total_count = (int) $wpdb->get_var( $sql );
        return $total_count;
    }

    /**
     * Returns array of items vendor.
     *
     * @since 1.0.0
     *
     * @return array
     */
    function get_items( $plugin, $number, $offset ) {
        $args = array(
            'post_type'      => 'shop_order',
            'orderby'        => 'ASC',
            'post_status'    => 'any',
            'offset'         => $offset,
            'posts_per_page' => $number,
            'post_parent'    => 0,
        );

        return get_posts( $args );
    }

    /**
     * Return class to handle migration.
     *
     * @since 1.0.0
     *
     * @return Class
     */
    function get_migration_class($plugin){
        switch ($plugin) {
            case 'wcfmmarketplace':
                return new WcfmOrderMigrator();
                break;

            case 'wcvendors':
                return new WcVendorsOrderMigrator();
                break;

            default:
                break;
        }
    }
}
