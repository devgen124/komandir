<?php

class LoginGenerator {
	private const DICT = [
		'а' => 'a',
		'б' => 'b',
		'в' => 'v',
		'г' => 'g',
		'д' => 'd',
		'е' => 'e',
		'ё' => 'yo',
		'ж' => 'zh',
		'з' => 'z',
		'и' => 'i',
		'й' => 'y',
		'к' => 'k',
		'л' => 'l',
		'м' => 'm',
		'н' => 'n',
		'о' => 'o',
		'п' => 'p',
		'р' => 'r',
		'с' => 's',
		'т' => 't',
		'у' => 'u',
		'ф' => 'f',
		'х' => 'kh',
		'ц' => 'ts',
		'ч' => 'tsh',
		'ш' => 'sh',
		'щ' => 'shy',
		'ъ' => '',
		'ы' => 'i',
		'ь' => '',
		'э' => 'e',
		'ю' => 'yu',
		'я' => 'ya',
	];

	private static function transliterate ( string $rword ) : string {
		$rword = mb_strtolower( $rword );
		return str_replace( array_keys(self::DICT), array_values(self::DICT), $rword );
	}

	public static function create ( string $firstname, string $lastname ) : string {
		return uniqid( self::transliterate( $firstname . $lastname ) );
	}
}

