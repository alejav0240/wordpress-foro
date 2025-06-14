<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'local');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', 'root');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '>IDgud=] Q#:;F#+#x!_sGx>iv$$[GOqaWVO4![dZd;7Ap^@wA4>hqBDrb^?1.Y=');
define('SECURE_AUTH_KEY', '7yZ;hN{l^35LAlE:g@Y@PGT~Kin&M+1)23af3UC3$0qTVUyjW1Y8]r]Xl)i;zBD8');
define('LOGGED_IN_KEY', 'KM,s->XXTlZqYq%;`d7]d{#]RfEKvIs0Hy+yfBr{9<K.>}ru6pdH|kCj#f2A/+I#');
define('NONCE_KEY', 'SBi{;J`j$#u}CQSnBZf/)B`* )P4Vt9M,|u$HR+nWSBXgU>=kgeK?<ixIeRkvNbn');
define('AUTH_SALT', '%l/Ry?r_<]CZE_p4?/ljoJ}/_Vp+#@dcFeH?9Q8,W[d%M43}>a)hy<Y9K;dt+T%3');
define('SECURE_AUTH_SALT', ' O4:f9;;WL|a,=aK?h2 P$@Y+C#5R3Xg#8((-MkQ:iW|{C_XzbvcX=,_r:w?z%85');
define('LOGGED_IN_SALT', 'ir>mJ%r&187yAM?N{)dlIxKFj2qjQr0Rkhw{CmQ%KGx@G.0>E_Wujh`gpVtTcWTp');
define('NONCE_SALT', '@?$e=y,%aD;gF;I+M<6$Q.he];/f@xwzAj*$_?zc0BO6JT69{Tg]8!;mPU*vV[Cm');
define('WP_CACHE_KEY_SALT', '$BE;~@ 5R|!$VC !-__SaS>.~^_+..Gq{rGOKXl*^MJRVOI~m1F^]f^y:(0]{67|');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */

// if ( ! defined( 'WP_DEBUG' ) ) {
// 	define( 'WP_DEBUG', false );
// }

if (!defined('WP_DEBUG')) {
	/* 	define( 'WP_DEBUG', false ); */
	define('WP_DEBUG', true);
	define('WP_DEBUG_LOG', true);
	define('WP_DEBUG_DISPLAY', false);
	@ini_set('display_errors', 0);

}

define('WP_ENVIRONMENT_TYPE', 'local');
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
