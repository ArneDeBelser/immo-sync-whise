<?php

namespace ADB\ImmoSyncWhise\Database;

class IWS_DetailsTable
{
    private $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        register_activation_hook(PLUGIN__FILE__, [$this, 'create']);
    }

    public function create()
    {
        $charset_collate = $this->wpdb->get_charset_collate();
        $table_name = $this->wpdb->prefix . 'iws_details';

        $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                post_id bigint(20) unsigned NOT NULL default '0',
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                detail_label varchar(255) NULL,
                detail_type varchar(255) NULL,
                detail_value varchar(255) NULL,
                detail_group varchar(255) NULL,
                detail_show varchar(255) NULL,
                UNIQUE KEY id (id),
                FOREIGN KEY (post_id) REFERENCES wp_posts (id) ON DELETE CASCADE
                ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function getDistinctDetailLabels()
    {
        $table_name = $this->wpdb->prefix . 'iws_details';

        $labels = $this->wpdb->get_col("SELECT DISTINCT detail_label FROM $table_name");
        return $labels;
    }

    public function getDistinctDetailTypes()
    {
        $table_name = $this->wpdb->prefix . 'iws_details';

        $types = $this->wpdb->get_col("SELECT DISTINCT detail_type FROM $table_name");
        return $types;
    }

    public function getDistinctDetailGroups()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iws_details';

        $groups = $wpdb->get_col("SELECT DISTINCT detail_group FROM $table_name");
        return $groups;
    }

    public function getDetailValueByLabel($post_id, $detail_label)
    {
        $table_name = $this->wpdb->prefix . 'iws_details';

        $query = $this->wpdb->prepare(
            "SELECT detail_value FROM $table_name WHERE post_id = %d AND detail_label = %s",
            $post_id,
            $detail_label
        );

        $detail_value = $this->wpdb->get_var($query);

        return $detail_value ? $detail_value : '';
    }
}
