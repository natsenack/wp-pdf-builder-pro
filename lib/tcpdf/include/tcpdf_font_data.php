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
	 * Arabic regular expression pattern
	 * @public
	 */
	public static $uni_RE_PATTERN_ARABIC = '/[\x{0600}-\x{06FF}]/u';

	/**
	 * RTL regular expression pattern
	 * @public
	 */
	public static $uni_RE_PATTERN_RTL = '/[\x{0590}-\x{05FF}\x{07C0}-\x{07FF}\x{FB1D}-\x{FB4F}]/u';

	/**
	 * UTF-8 to Latin character mapping
	 * @public
	 */
	public static $uni_utf8tolatin = array(
		0x00A0 => 0x20, // NO-BREAK SPACE -> SPACE
		0x00A1 => 0x21, // INVERTED EXCLAMATION MARK -> EXCLAMATION MARK
		0x00A2 => 0x63, // CENT SIGN -> LATIN SMALL LETTER C
		0x00A3 => 0x50, // POUND SIGN -> LATIN CAPITAL LETTER P
		0x00A4 => 0x24, // CURRENCY SIGN -> DOLLAR SIGN
		0x00A5 => 0x59, // YEN SIGN -> LATIN CAPITAL LETTER Y
		0x00A6 => 0x7C, // BROKEN BAR -> VERTICAL LINE
		0x00A7 => 0x53, // SECTION SIGN -> LATIN CAPITAL LETTER S
		0x00A8 => 0x22, // DIAERESIS -> QUOTATION MARK
		0x00A9 => 0x43, // COPYRIGHT SIGN -> LATIN CAPITAL LETTER C
		0x00AA => 0x61, // FEMININE ORDINAL INDICATOR -> LATIN SMALL LETTER A
		0x00AB => 0x22, // LEFT-POINTING DOUBLE ANGLE QUOTATION MARK -> QUOTATION MARK
		0x00AC => 0x2D, // NOT SIGN -> HYPHEN-MINUS
		0x00AD => 0x2D, // SOFT HYPHEN -> HYPHEN-MINUS
		0x00AE => 0x52, // REGISTERED SIGN -> LATIN CAPITAL LETTER R
		0x00AF => 0x2D, // MACRON -> HYPHEN-MINUS
		0x00B0 => 0x6F, // DEGREE SIGN -> LATIN SMALL LETTER O
		0x00B1 => 0x2B, // PLUS-MINUS SIGN -> PLUS SIGN
		0x00B2 => 0x32, // SUPERSCRIPT TWO -> DIGIT TWO
		0x00B3 => 0x33, // SUPERSCRIPT THREE -> DIGIT THREE
		0x00B4 => 0x27, // ACUTE ACCENT -> APOSTROPHE
		0x00B5 => 0x75, // MICRO SIGN -> LATIN SMALL LETTER U
		0x00B6 => 0x50, // PILCROW SIGN -> LATIN CAPITAL LETTER P
		0x00B7 => 0x2E, // MIDDLE DOT -> FULL STOP
		0x00B8 => 0x2C, // CEDILLA -> COMMA
		0x00B9 => 0x31, // SUPERSCRIPT ONE -> DIGIT ONE
		0x00BA => 0x6F, // MASCULINE ORDINAL INDICATOR -> LATIN SMALL LETTER O
		0x00BB => 0x22, // RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK -> QUOTATION MARK
		0x00BC => 0x31, // VULGAR FRACTION ONE QUARTER -> DIGIT ONE
		0x00BD => 0x31, // VULGAR FRACTION ONE HALF -> DIGIT ONE
		0x00BE => 0x33, // VULGAR FRACTION THREE QUARTERS -> DIGIT THREE
		0x00BF => 0x3F, // INVERTED QUESTION MARK -> QUESTION MARK
		// Add more mappings as needed
	);

	/**
	 * Character encoding mapping
	 * @public
	 */
	public static $encmap = array(
		'cp1252' => array(
			0x20AC => 0x80, // EURO SIGN
			0x201A => 0x82, // SINGLE LOW-9 QUOTATION MARK
			0x0192 => 0x83, // LATIN SMALL LETTER F WITH HOOK
			0x201E => 0x84, // DOUBLE LOW-9 QUOTATION MARK
			0x2026 => 0x85, // HORIZONTAL ELLIPSIS
			0x2020 => 0x86, // DAGGER
			0x2021 => 0x87, // DOUBLE DAGGER
			0x02C6 => 0x88, // MODIFIER LETTER CIRCUMFLEX ACCENT
			0x2030 => 0x89, // PER MILLE SIGN
			0x0160 => 0x8A, // LATIN CAPITAL LETTER S WITH CARON
			0x2039 => 0x8B, // SINGLE LEFT-POINTING ANGLE QUOTATION MARK
			0x0152 => 0x8C, // LATIN CAPITAL LIGATURE OE
			0x017D => 0x8E, // LATIN CAPITAL LETTER Z WITH CARON
			0x2018 => 0x91, // LEFT SINGLE QUOTATION MARK
			0x2019 => 0x92, // RIGHT SINGLE QUOTATION MARK
			0x201C => 0x93, // LEFT DOUBLE QUOTATION MARK
			0x201D => 0x94, // RIGHT DOUBLE QUOTATION MARK
			0x2022 => 0x95, // BULLET
			0x2013 => 0x96, // EN DASH
			0x2014 => 0x97, // EM DASH
			0x02DC => 0x98, // SMALL TILDE
			0x2122 => 0x99, // TRADE MARK SIGN
			0x0161 => 0x9A, // LATIN SMALL LETTER S WITH CARON
			0x203A => 0x9B, // SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
			0x0153 => 0x9C, // LATIN SMALL LIGATURE OE
			0x017E => 0x9E, // LATIN SMALL LETTER Z WITH CARON
			0x0178 => 0x9F, // LATIN CAPITAL LETTER Y WITH DIAERESIS
		)
	);

	/**
	 * Unicode character types for bidirectional algorithm
	 * @public
	 */
	public static $uni_type = array();

	/**
	 * Right-to-Left Embedding (RLE) character
	 * @public
	 */
	public static $uni_RLE = 0x202B;

	/**
	 * Left-to-Right Embedding (LRE) character
	 * @public
	 */
	public static $uni_LRE = 0x202A;

	/**
	 * Right-to-Left Override (RLO) character
	 * @public
	 */
	public static $uni_RLO = 0x202E;

	/**
	 * Left-to-Right Override (LRO) character
	 * @public
	 */
	public static $uni_LRO = 0x202D;

	/**
	 * Pop Directional Formatting (PDF) character
	 * @public
	 */
	public static $uni_PDF = 0x202C;

	/**
	 * Unicode character mirroring map
	 * @public
	 */
	public static $uni_mirror = array();

	/**
	 * Identity mapping for horizontal writing
	 * @public
	 */
	public static $uni_identity_h = array();

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

