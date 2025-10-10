<?php
//============================================================+
// File name   : tcpdf_font_data.php
// Version     : 1.0.000
// Begin       : 2008-01-01
// Last Update : 2014-12-10
// Author      : Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com
// License     : GNU-LGPL v3 (http://www.gnu.org/copyleft/lesser.html)
// -------------------------------------------------------------------
// Copyright (C) 2008-2014 Nicola Asuni - Tecnick.com LTD
//
// This file is part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with TCPDF. If not, see <http://www.gnu.org/licenses/>.
//
// See LICENSE.TXT file for more information.
// -------------------------------------------------------------------
//
// DESCRIPTION :
//   Static font methods and data for TCPDF library.
//
//============================================================+

/**
 * @file
 * Static font methods and data for TCPDF library.
 * @package com.tecnick.tcpdf
 * @author Nicola Asuni
 * @version 1.0.000
 */

// Prevent direct access to this file
if (!defined('K_TCPDF_VERSION')) {
	die('Permission denied.');
}

/**
 * @class TCPDF_FONT_DATA
 * Static font methods and data for TCPDF library.
 * @package com.tecnick.tcpdf
 * @version 1.0.000
 * @author Nicola Asuni - info@tecnick.com
 */
class TCPDF_FONT_DATA {

	/**
	 * Unicode data
	 * @protected
	 */
	protected static $unicode = array();

	/**
	 * Return the unicode data array for the specified character
	 * @param $char (int) character code
	 * @return mixed character unicode data or false if not exist
	 * @public static
	 */
	public static function getUnicodeData($char) {
		if (isset(self::$unicode[$char])) {
			return self::$unicode[$char];
		}
		return false;
	}

	/**
	 * Set the unicode data array
	 * @param $unicode (array) array containing unicode data
	 * @public static
	 */
	public static function setUnicodeData($unicode) {
		self::$unicode = $unicode;
	}

} // END OF TCPDF_FONT_DATA CLASS

//============================================================+
// END OF FILE
//============================================================+