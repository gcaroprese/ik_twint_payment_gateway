<?php
/*
Plugin Name: IK Twint Payment Gateway
Plugin URI: https://selectdata.ch/
Description: Twint Payment Gateway
Version: 1.1.3
Author: Selectdata / Gabriel Caroprese
Author URI: https://selectdata.ch/
Requires at least: 5.3
Requires PHP: 7.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$ik_twintpg_dir = dirname( __FILE__ );
$ik_twintpg_public_dir = plugin_dir_url(__FILE__ );
define( 'IK_TWINTPG_PLUGIN_DIR', $ik_twintpg_dir);
define( 'IK_TWINTPG_PLUGIN_DIR_PUBLIC', $ik_twintpg_public_dir);

require_once($ik_twintpg_dir . '/include/class.php');
require_once($ik_twintpg_dir . '/include/hooks.php');
register_activation_hook( __FILE__, 'ik_dirdatos_activacion' );

?>