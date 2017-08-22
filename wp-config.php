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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'db624732694');

/** MySQL database username */
define('DB_USER', 'dbo624732694');

/** MySQL database password */
define('DB_PASSWORD', 'R3AlL1ght1ng$$');

/** MySQL hostname */
define('DB_HOST', 'db624732694.db.1and1.com');

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
define('AUTH_KEY',         'Mp;,YHyg|{9O`-#)S8#kBxpOFPjCL[QO}XG[,mp|CLIN6-nq1,@r(]=J2(ww-J]_');
define('SECURE_AUTH_KEY',  'y~lzn_h$kJYCm&tlC2e;dRC?Kh<m+E*OC7<a^5U>}b=y{/,=Ygx(>-FQ5sKX.5?4');
define('LOGGED_IN_KEY',    'jG{cHk;r+O~VCdN%{Yk]b`{wb@hyiKb$`%aX)+Vqp<-dm5bc&*7aA$3f8uSAWk?l');
define('NONCE_KEY',        '-WighX(k{CjU31V-a.002$i=A!?+B5t~%y.Q.0DAsGtD|tSK7w]hov%,2.w-u|J=');
define('AUTH_SALT',        'sDi6dM2IDIsn==_hNm$cy[a?j,?J]^71Vv08j;Q~S(5(#)|S~D3N-|T+;bGgq2V_');
define('SECURE_AUTH_SALT', 'bh#(Jq|ZwXN;k V||SK)uMa]zT=17! *u+x,m]q#P<4;n:;#Sk5@=[Hn-K=U9nRD');
define('LOGGED_IN_SALT',   'OcN2Y-e^%=s-ytzEC/g|r#f]uP/ &322cE -+XO:o- @%FOsJ5CHiEyS~`b-Qv/T');
define('NONCE_SALT',       '}E,vs ,V9SP-CTv7Gh&-4-rw1vY4`B[4iJ{G-vQd>2@+EX|gp/hkqwyJp@7a9C%P');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
