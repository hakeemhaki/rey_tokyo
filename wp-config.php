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
define( 'DB_NAME', 'rey_tokyo' );

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
define( 'AUTH_KEY',         ',<yE/;cLpGd|d!@wk;<hYd`6]8hMVo%<5&}ma$qUyR}o2R?jt)H,.$vb3=IoPE],' );
define( 'SECURE_AUTH_KEY',  '$NMF5@u-My,<h#k&KIp-[>#e,O_d02E}DrC.@Xw7=6]n0U5-F(s3Xm> ?a(3!yB}' );
define( 'LOGGED_IN_KEY',    ')LqQXfg}=|eC=UF-Q`9hMN&_gn;}GXEY@VMB+{T0xAv5r;D5|EL& CI5_DR<`(HE' );
define( 'NONCE_KEY',        'wJmDub+uRz)r(r,|u7/osk.^{=ap}G|EtJbaJ+71xGfX$IS%v /nAmK#hU<rYb?:' );
define( 'AUTH_SALT',        'p[rn(1l:3)7_v1+zyBGc}4mcGv{)s6][C=oV2[*^yeg^8&$kz`9)bY7Wxc6M*/|2' );
define( 'SECURE_AUTH_SALT', 'O-G*%J?*TzTF0Q*q,~W[~8_UaCJ#-dHKRix(;lIyO@+m0.P,k0y8V!X)4mLk^Lo?' );
define( 'LOGGED_IN_SALT',   't?,J6u8Upnh@.&NMojWim=8L<}GX&jd1z|2{iF.,0k|PV%Odx-m6wozqfiHMv{99' );
define( 'NONCE_SALT',       'RR@{d u`pEt-3iy[ A?=cgF14jw%Uxr^FgmGABC_ D=s){tQocM#a<8`x6~Dua}n' );

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
