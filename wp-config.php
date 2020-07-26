<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'coolnerd' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '12345' );

/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '/ME0&ufagr0Wnd_gm%1SEfM$)(mdY?wYq.Q*FY~!7NQ9Plrf) 1QdUnj,|_7b^Wv' );
define( 'SECURE_AUTH_KEY',  ':Upu~Ueaj}F[q.yA#{a|>YQN%~TagK$FlDql51%tLm`5rvO&*kForF.KWMAG!^LN' );
define( 'LOGGED_IN_KEY',    'WodenU7XAGeUAy?mrv4$60oW$VpLX;0B+bGnghe/qe!^a0a>1`ElpAX ;JeoY@+N' );
define( 'NONCE_KEY',        '#Zn$;`w1y$*kGQ(Xr>L:uGoCOUR7YJi A3c2M3t.y;XWHLMklU[=I2n$lNm2;fp%' );
define( 'AUTH_SALT',        '|<*t~b!78=0zG%|0`%S;/ +gPg.dMjPH_Me1IH/3i_A_Vn4aylM.v@W_aHz,JiCX' );
define( 'SECURE_AUTH_SALT', '%HQ$jn4;6>j4!hY438ko;)?#{hUFuK+)Lf9##PgcSH*5+ggU<uT/)yn0Mn}6>n%R' );
define( 'LOGGED_IN_SALT',   'smWbu;Zs27WC2`N#]`hd7SxoVKN-Ko>TbwBsc,uA8) 5Az99KTSvK59 6r8{0fhR' );
define( 'NONCE_SALT',       'Xw;#35{1S-%H{G3ej5Tj)3.m3$e+jPS}*>=-Y=O<}vdY%MY`R<}ge2leh.bG[{&5' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );
define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
