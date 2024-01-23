<?php

/*
 * Plugin Name:       Immo Sync Whise
 * Plugin URI:        
 * Description:       Elevate your real estate WordPress site with the power of Whise through our Immo Sync plugin. This seamless integration simplifies the connection between your website and the Whise API, allowing you to effortlessly incorporate real-time property data and listings, enhancing your visitors' property search experience.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.3
 * Author:            De Belser Arne
 * Author URI:        https://www.arnedebelser.be/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       immo-sync-whise
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/autoload.php';

use ADB\ImmoSyncWhise\Admin\Settings;
use ADB\ImmoSyncWhise\Api;
use ADB\ImmoSyncWhise\Database\IWS_DetailsTable;
use ADB\ImmoSyncWhise\Plugin;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Whise\Api\WhiseApi;

define('ISW_VERSION', fileatime(__DIR__));
define('ISW_PATH', __DIR__);
define('PLUGIN__FILE__', __FILE__);

new IWS_DetailsTable();

/**
 * Get main plugin class instance
 *
 * @return Plugin
 */
function immo_sync_whise()
{
    static $immo_sync_whise;

    if (!$immo_sync_whise) {
        $immo_sync_whise = new Plugin();

        do_action("immo_sync_whise", $immo_sync_whise);
    }

    return $immo_sync_whise;
}

/**
 * Bind concrete implementations in the Laravel container
 */
add_action('immo_sync_whise', function ($immo_sync_whise) {
    $immo_sync_whise->bind(LoggerInterface::class, function ($immo_sync_whise, $args) {
        return (new Logger('operations'))->pushHandler(new StreamHandler(__DIR__ . '/logs/operations.log', Level::Debug));
    });

    $immo_sync_whise->bind(Api::class, function ($immo_sync_whise, $args) {
        return new Api(connection: new WhiseApi(), whiseUser: Settings::getSetting('whise_user'), whisePassword: Settings::getSetting('whise_password'));
    });
});

immo_sync_whise(); // Run the main plugin functionality
