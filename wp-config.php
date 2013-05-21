<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_nidhal');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'B-PxCPx*+)4. GZvO:^m.T8E0Yz&XnT]I&:{Htk!|:MmzJQ2$%Ub#+gsY,f|P!~l');
define('SECURE_AUTH_KEY',  'Yjy!^>?^|srr]1ETm-S $#-z*Rk;-c1sRPy]lz1S(Y>Z@2b[.r}Co}rxX_=yEMLm');
define('LOGGED_IN_KEY',    '<oF2CHSfTTfe=nQ4+12LObX;(VFwJe7X-;E+95R T6USc1;a#8En-vs,N1^+O!j(');
define('NONCE_KEY',        '4h#+pv+fRLX~]+}4`R4=~)+/I4DcwiE*e*7EH|b>ORQ(u>:@}5XxoxRXeP|t?k&5');
define('AUTH_SALT',        '&!n*bAT[VJC:#?5XBbzk-peLD==[{tL@$ZY653c(=BcC%uJ(~*#!~NBt8X#Kr6yY');
define('SECURE_AUTH_SALT', '32hKG*ZRynE68y%B9hjMdiHos3@`^Pfdf9O8~:+L=fs]0ys (@[dK+rm| ki%pc,');
define('LOGGED_IN_SALT',   'n!d;.,P%W?ue*-hi3vR`tqBGS+Io }lL{.`iJ(/x5$F|iOg-f0dX`:{ZWMX^gGxI');
define('NONCE_SALT',       'ov#ll$*lP08$QW#%V9E(G;*Z_yK|k(H.8lF[h-rM]swUR/LT6db%G*gRcEeFOG3g');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');


define('FS_METHOD', 'direct');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
