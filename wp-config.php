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
define( 'DB_NAME', 'players_voice' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         ']qNut5;$mQ8UqBd<x,TvT~)E9)Uk9c[/Tmj;66dw#::^3e.|Rr}CokZu-N?ZkIlC' );
define( 'SECURE_AUTH_KEY',  'yP,y~,)(N}}]3V06Ui xM<>FpTwjPzjlyGopgrP|fm`<8#MDE/w5m1gf#Z^zy*Mw' );
define( 'LOGGED_IN_KEY',    '9<*{3 ^`~?O;T^-mli}+deHc([g~rQYW|$AJI5_o`_CfsT_V:XDb31IriHtaeVOk' );
define( 'NONCE_KEY',        'BX$@EaORDK%Wr}h=YQH_;]*d)0/o]El q<|;IUK{<8jkp]o/b-r!H8ca;*;>l;<q' );
define( 'AUTH_SALT',        '>j 4s/oGK.!]l2O 8PH_f!/k09d#9NoF@z?C71*i[HPzSeC<&cIKhjb5|sYlf9~^' );
define( 'SECURE_AUTH_SALT', 'oQXvcB8|d]=~c>c8h+3g=Z+~].>:vcvTz>L)LnooZ^@xq|:8wv9J2K#i*SdvkWZ8' );
define( 'LOGGED_IN_SALT',   '.P3WncbDna/g_mvZN=I;e7L6N]8S9v^cye]AJIxbL3{B4-dl=(@R+%Zh~/UR`gLz' );
define( 'NONCE_SALT',       'Lcs,](oTW5dH=/>ySVuI{r1t{G@Q,qfp+$Jq8Z[5%3 {27dRyO)l -(Ar6:n,dfP' );

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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
