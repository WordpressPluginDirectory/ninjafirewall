<?php
/*
 +=====================================================================+
 |    _   _ _        _       _____ _                        _ _        |
 |   | \ | (_)_ __  (_) __ _|  ___(_)_ __ _____      ____ _| | |       |
 |   |  \| | | '_ \ | |/ _` | |_  | | '__/ _ \ \ /\ / / _` | | |       |
 |   | |\  | | | | || | (_| |  _| | | | |  __/\ V  V / (_| | | |       |
 |   |_| \_|_|_| |_|/ |\__,_|_|   |_|_|  \___| \_/\_/ \__,_|_|_|       |
 |                |__/                                                 |
 |  (c) NinTechNet Limited ~ https://nintechnet.com/                   |
 +=====================================================================+
*/

if ( class_exists('NinjaFirewall_helpers') ) {
	return;
}

class NinjaFirewall_helpers {


	/**
	 * Retrieve and return matching files from a directory.
	 * Replacement for the PHP glob() function to make file search compatible with remote files.
	 */
	public static function nfw_glob( $directory, $regex, $pathname = false, $sortname = true ) {

		$list = [];

		foreach ( new DirectoryIterator( $directory ) as $finfo ) {
			if (! $finfo->isDot() && preg_match("`$regex`", $finfo->getFilename() ) ) {
				if ( $pathname ) {
					$list[] = $finfo->getPathname();
				} else {
					$list[] = $finfo->getFilename();
				}
			}
		}
		if ( $sortname === true ) {
			asort( $list );
		}

		return $list;
	}


	/**
	 * Retrieve and return matching files from a directory, recursively.
	 * Replacement for the PHP glob() function to make file search compatible with remote files.
	 */
	public static function nfw_glob_recursive( $directory, $regex, $pathname = false ) {

		$list = [];

		$dir_iterator = new RecursiveDirectoryIterator( $directory );
		$iterator = new RecursiveIteratorIterator( $dir_iterator );

		foreach ( $iterator as $finfo ) {
			if ( preg_match("`$regex`", $finfo->getFilename() ) ) {
				if ( $pathname ) {
					$list[] = $finfo->getPathname();
				} else {
					$list[] = $finfo->getFilename();
				}
			}
		}
		return $list;
	}

}

// ---------------------------------------------------------------------
// EOF
