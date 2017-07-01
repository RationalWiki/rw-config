<?php

// MW entry point wrapper

function rwWrapper() {
	if ( !isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
		throw new Exception( 'SCRIPT_FILENAME is not set' );
	}
	$script = basename( $_SERVER['SCRIPT_FILENAME'] );
	$base = realpath( __DIR__ . '/..' );
	define( 'MW_INSTALL_PATH', "$base/core" );
	putenv( 'MW_INSTALL_PATH=' . MW_INSTALL_PATH );
	define( 'MW_CONFIG_FILE', "$base/config/RWSettings.php" );
	return MW_INSTALL_PATH  . '/' . $script;
}

require rwWrapper();
