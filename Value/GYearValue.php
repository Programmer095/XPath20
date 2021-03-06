<?php
/**
 * XPath 2.0 for PHP
 *  _                      _     _ _ _
 * | |   _   _  __ _ _   _(_) __| (_) |_ _   _
 * | |  | | | |/ _` | | | | |/ _` | | __| | | |
 * | |__| |_| | (_| | |_| | | (_| | | |_| |_| |
 * |_____\__, |\__, |\__,_|_|\__,_|_|\__|\__, |
 *       |___/    |_|                    |___/
 *
 * @author Bill Seddon
 * @version 0.9
 * @Copyright (C) 2017 Lyquidity Solutions Limited
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace lyquidity\XPath2\Value;

use lyquidity\XPath2\Properties\Resources;
use lyquidity\XPath2\DOM\XmlSchema;
use lyquidity\xml\interfaces\IXmlSchemaType;
use lyquidity\XPath2\CoreFuncs;
use lyquidity\xml\schema\SchemaTypes;
use lyquidity\XPath2\XPath2Exception;

/**
 * GYearValue (public)
 */
class GYearValue extends DateTimeValueBase implements IXmlSchemaType
{
	/**
	 * CLASSNAME
	 * @var string
	 */
	public static $CLASSNAME = "lyquidity\XPath2\Value\GYearValue";

	/**
	 * Constructor
	 * @param bool $sign
	 * @param \DateTime $value
	 */
	public  function __construct( $sign, $value )
	{
		// parent::__construct( $sign, $value );
		$this->fromDateBase( $sign, $value );
	}

	/**
	 * Returns a schema type for the proxy value
	 * @return XmlSchemaType
	 */
	public function getSchemaType()
	{
		return XmlSchema::$GYear;
	}

	/**
	 * Returns the contained value
	 * @return GYearValue
	 */
	public function getValue()
	{
		return $this->Value;
	}

	/**
	 * Equals
	 * @param object $obj
	 * @return bool
	 */
	public function Equals( $obj )
	{
		if ( ! $obj instanceof GYearValue )
		{
			return false;
		}

		/**
		 * @var DateTimeValueBase $other
		 */
		$other = $obj;
		return $this->ToString( null ) == $other->ToString( null );
	}

	/**
	 * ToString
	 * @param IFormatProvider $provider
	 * @return string
	 */
	public function ToString( $provider = null )
	{
		$result = "";
		if ( $this->S ) $result .= "-";
		if ( $this->IsLocal )
	        $result .= $this->Value->format("Y");
	    else
	        if ( $this->Value->getOffset() == 0 )
	            $result .= $this->Value->format("Y\Z");
	        else
	            $result .= $this->Value->format("YP");

	    return $result;
	}

	/**
	 * Return a stringified version of the object
	 * @return string
	 */
	public function __toString()
	{
		return $this->ToString();
	}

	/**
	 * Parse
	 * @param string $text
	 * @return GYearValue
	 */
	public static function Parse( $text )
	{
		$text = strtoupper( trim( $text ) );

		$result = preg_match( "/^(?<sign>-?)(?<year>\d{4,4})(?<offset>(?=[+\-a-zA-Z])(([+\-]\d{2}(:\d{2}))|Z|((\?!-|\\+)(?i)[^0-9].{3,})))?$/i", $text, $matches );
		if ( ! $result )
		{
			throw XPath2Exception::withErrorCodeAndParams( "FORG0001", Resources::FORG0001, array( $text, "xs:gYear" ) );
		}

		$error =  empty( $matches['year'] ) ||
				  ( CoreFuncs::$strictGregorian && $matches['year'] < 1532 );

		$offsetMatches = null;
		if ( ! $error && ! empty( $matches['offset'] ) )
		{
			$error = $matches['offset'] != "Z" &&
			! in_array( $matches['offset'], timezone_identifiers_list() ) &&
			! preg_match( "/^[+-](?<hours>\d{2})(:(?<minutes>\d{2}))?$/", $matches['offset'], $offsetMatches );

			if ( ! $error && ! is_null( $offsetMatches ) )
			{
				$error = empty( $offsetMatches['hours'] ) || $offsetMatches['hours'] > 14 ||
				empty( $offsetMatches['minutes'] ) || $offsetMatches['minutes'] > 59;
			}
		}

		if ( $error )
		{
			throw XPath2Exception::withErrorCodeAndParams( "FORG0001", Resources::FORG0001, array( $text, "xs:gYear" ) );
		}

		/**
		 * @var bool $s
		 */
	    $s = SchemaTypes::startsWith( $text, "-" );
		if ( $s ) $text = substr( $text, 1 );
		$dateTime = \DateTime::createFromFormat( "YO", $text );
		if ( $dateTime )
		{
			return new GYearValue( $s, $dateTime );
		}

		$dateTime = \DateTime::createFromFormat( "Y\Z", $text );
		if ( $dateTime )
		{
			return new GYearValue( $s, $dateTime );
		}

		$dateTime = \DateTime::createFromFormat( "Y", $text );
		if ( $dateTime )
		{
			return new GYearValue( $s, $dateTime );
		}

		throw XPath2Exception::withErrorCodeAndParams( "FORG0001", Resources::FORG0001, array( $text, "xs:gYear" ) );
	}

	/**
	 * Unit tests
	 */
	public static function tests()
	{
		$gYear = GYearValue::Parse( "2009Z" );
		echo "{$gYear->ToString()}\n";
	}

}

?>
