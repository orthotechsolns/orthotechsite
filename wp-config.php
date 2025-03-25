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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'PN va&x%$z^y[7caeh&qW;&_K&e^%gLG];n:9v,=w>S6p{ZZfylVpnmknQLbYjs ' );
define( 'SECURE_AUTH_KEY',   'lfj6W3!hT)7DJ.{uxlFnLS-.b!h=c)*r>]W1sT?K5Gn##]R3D.J4+U>`PE =d.y*' );
define( 'LOGGED_IN_KEY',     'k!PXngw.tuYS.g%^y?.qdvrC2=0W?pDx>>S$/^_*4 9s:FO.oGQZI[y^4vJpa(X5' );
define( 'NONCE_KEY',         '+R mO `#*v7X]zohDF-uQxLNfOr/OSAg>iFp*tsYebx@>w(+AJzq@;N/AgLQh14:' );
define( 'AUTH_SALT',         'srKUTl~:;)WS%c%!+<J%jfCZ<je.&+sCXwiJ3NX]+dUc8@?dxE:Uv*8. 7k7D/Bb' );
define( 'SECURE_AUTH_SALT',  'eqt{Cepq$Kg)s0T#5Gz#vF@D!Eca:FEv>o}SNws`*gAt:%a~tm[23r/Ese^et?C^' );
define( 'LOGGED_IN_SALT',    '@qnVm!gig-.2W6%7<)V;pr_X1-?!0<%TQBiZ]lt:E Op.9Cf9YnVl+@QPdH:gvr`' );
define( 'NONCE_SALT',        'K]?jDL5P5u{VGCIQ- 1`%hUxpJNBLL)?S>sS zKNf?W/dqIkFFVk9G30`fFmOQL3' );
define( 'WP_CACHE_KEY_SALT', 'jAFj .y*<zzc6;Ef4q3+oSS=wt hNf!:{7a$,eJU3eK<TDy?CFKnyW#65xDl~U@*' );


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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
