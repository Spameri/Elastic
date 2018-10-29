<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\ValidateMapping;


/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-tokenizers.html
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-analyzers.html
 */
class AllowedValues
{

	//
	// TYPES
	//

	// Text types
	public const TYPE_TEXT = 'text';
	public const TYPE_KEYWORD = 'keyword';

	// Boolean types
	public const TYPE_BOOLEAN = 'boolean';

	// Numeric types
	public const TYPE_INTEGER = 'integer';
	public const TYPE_LONG = 'long';
	public const TYPE_SHORT = 'short';
	public const TYPE_BYTE = 'byte';
	public const TYPE_DOUBLE = 'double';
	public const TYPE_FLOAT = 'float';
	public const TYPE_HALF_FLOAT = 'half_float';
	public const TYPE_SCALED_FLOAT = 'scaled_float';

	// Date types
	public const TYPE_DATE = 'date';

	// Range types
	public const TYPE_INTEGER_RANGE = 'integer_range';
	public const TYPE_FLOAT_RANGE   = 'float_range';
	public const TYPE_LONG_RANGE    = 'long_range';
	public const TYPE_DOUBLE_RANGE  = 'double_range';
	public const TYPE_DATE_RANGE    = 'date_range';

	// Complex types
	public const TYPE_OBJECT = 'object';
	public const TYPE_NESTED = 'nested';

	// Geo types
	public const TYPE_GEO_POINT = 'geo_point';
	public const TYPE_GEO_SHAPE = 'geo_shape';

	// Special types
	public const TYPE_IP = 'ip';
	public const TYPE_COMPLETION = 'completion';
	public const TYPE_TOKEN_COUNT = 'token_count';
	public const TYPE_MURMUR3 = 'murmur3';
	public const TYPE_PERCOLATOR = 'percolator';
	public const TYPE_JOIN = 'join';
	public const TYPE_ALIAS = 'alias';

	public const TYPES = [
		self::TYPE_TEXT    => self::TYPE_TEXT,
		self::TYPE_KEYWORD => self::TYPE_KEYWORD,

		self::TYPE_BOOLEAN => self::TYPE_BOOLEAN,

		self::TYPE_INTEGER      => self::TYPE_INTEGER,
		self::TYPE_LONG         => self::TYPE_LONG,
		self::TYPE_SHORT        => self::TYPE_SHORT,
		self::TYPE_BYTE         => self::TYPE_BYTE,
		self::TYPE_DOUBLE       => self::TYPE_DOUBLE,
		self::TYPE_FLOAT        => self::TYPE_FLOAT,
		self::TYPE_HALF_FLOAT   => self::TYPE_HALF_FLOAT,
		self::TYPE_SCALED_FLOAT => self::TYPE_SCALED_FLOAT,

		self::TYPE_DATE => self::TYPE_DATE,

		self::TYPE_INTEGER_RANGE => self::TYPE_INTEGER_RANGE,
		self::TYPE_FLOAT_RANGE   => self::TYPE_FLOAT_RANGE,
		self::TYPE_LONG_RANGE    => self::TYPE_LONG_RANGE,
		self::TYPE_DOUBLE_RANGE  => self::TYPE_DOUBLE_RANGE,
		self::TYPE_DATE_RANGE    => self::TYPE_DATE_RANGE,

		self::TYPE_OBJECT => self::TYPE_OBJECT,
		self::TYPE_NESTED => self::TYPE_NESTED,

		self::TYPE_GEO_POINT => self::TYPE_GEO_POINT,
		self::TYPE_GEO_SHAPE => self::TYPE_GEO_SHAPE,

		self::TYPE_IP          => self::TYPE_IP,
		self::TYPE_COMPLETION  => self::TYPE_COMPLETION,
		self::TYPE_TOKEN_COUNT => self::TYPE_TOKEN_COUNT,
		self::TYPE_MURMUR3     => self::TYPE_MURMUR3,
		self::TYPE_PERCOLATOR  => self::TYPE_PERCOLATOR,
		self::TYPE_JOIN        => self::TYPE_JOIN,
		self::TYPE_ALIAS       => self::TYPE_ALIAS,
	];

	//
	// ANALYZERS
	//

	public const ANALYZER_STANDARD = 'standard';
	public const ANALYZER_SIMPLE = 'simple';
	public const ANALYZER_WHITESPACE = 'whitespace';
	public const ANALYZER_STOP = 'stop';
	public const ANALYZER_KEYWORD = 'keyword';
	public const ANALYZER_PATTERN = 'pattern';
	public const ANALYZER_FINGERPRINT = 'fingerprint';


	public const ANALYZER_ARABIC = 'arabic';
	public const ANALYZER_ARMENIAN = 'armenian';
	public const ANALYZER_BASQUE = 'basque';
	public const ANALYZER_BENGALI = 'bengali';
	public const ANALYZER_BRAZILIAN = 'brazilian';
	public const ANALYZER_BULGARIAN = 'bulgarian';
	public const ANALYZER_CATALAN = 'catalan';
	public const ANALYZER_CJK = 'cjk';
	public const ANALYZER_CZECH = 'czech';
	public const ANALYZER_DANISH = 'danish';
	public const ANALYZER_DUTCH = 'dutch';
	public const ANALYZER_ENGLISH = 'english';
	public const ANALYZER_FINNISH = 'finnish';
	public const ANALYZER_FRENCH = 'french';
	public const ANALYZER_GALICIAN = 'galician';
	public const ANALYZER_GERMAN = 'german';
	public const ANALYZER_GREEK = 'greek';
	public const ANALYZER_HINDI = 'hindi';
	public const ANALYZER_HUNGARIAN = 'hungarian';
	public const ANALYZER_INDONESIAN = 'indonesian';
	public const ANALYZER_IRISH = 'irish';
	public const ANALYZER_ITALIAN = 'italian';
	public const ANALYZER_LATVIAN = 'latvian';
	public const ANALYZER_LITHUANIAN = 'lithuanian';
	public const ANALYZER_NORWEGIAN = 'norwegian';
	public const ANALYZER_PERSIAN = 'persian';
	public const ANALYZER_PORTUGUESE = 'portuguese';
	public const ANALYZER_ROMANIAN = 'romanian';
	public const ANALYZER_RUSSIAN = 'russian';
	public const ANALYZER_SORANI = 'sorani';
	public const ANALYZER_SPANISH = 'spanish';
	public const ANALYZER_SWEDISH = 'swedish';
	public const ANALYZER_TURKISH = 'turkish';
	public const ANALYZER_THAI = 'thai';

	public const ANALYZERS = [
		self::ANALYZER_STANDARD => self::ANALYZER_STANDARD,
		self::ANALYZER_SIMPLE => self::ANALYZER_SIMPLE,
		self::ANALYZER_WHITESPACE => self::ANALYZER_WHITESPACE,
		self::ANALYZER_STOP => self::ANALYZER_STOP,
		self::ANALYZER_KEYWORD => self::ANALYZER_KEYWORD,
		self::ANALYZER_PATTERN => self::ANALYZER_PATTERN,
		self::ANALYZER_FINGERPRINT => self::ANALYZER_FINGERPRINT,

		self::ANALYZER_ARABIC => self::ANALYZER_ARABIC,
		self::ANALYZER_ARMENIAN => self::ANALYZER_ARMENIAN,
		self::ANALYZER_BASQUE => self::ANALYZER_BASQUE,
		self::ANALYZER_BENGALI => self::ANALYZER_BENGALI,
		self::ANALYZER_BRAZILIAN => self::ANALYZER_BRAZILIAN,
		self::ANALYZER_BULGARIAN => self::ANALYZER_BULGARIAN,
		self::ANALYZER_CATALAN => self::ANALYZER_CATALAN,
		self::ANALYZER_CJK => self::ANALYZER_CJK,
		self::ANALYZER_CZECH => self::ANALYZER_CZECH,
		self::ANALYZER_DANISH => self::ANALYZER_DANISH,
		self::ANALYZER_DUTCH => self::ANALYZER_DUTCH,
		self::ANALYZER_ENGLISH => self::ANALYZER_ENGLISH,
		self::ANALYZER_FINNISH => self::ANALYZER_FINNISH,
		self::ANALYZER_FRENCH => self::ANALYZER_FRENCH,
		self::ANALYZER_GALICIAN => self::ANALYZER_GALICIAN,
		self::ANALYZER_GERMAN => self::ANALYZER_GERMAN,
		self::ANALYZER_GREEK => self::ANALYZER_GREEK,
		self::ANALYZER_HINDI => self::ANALYZER_HINDI,
		self::ANALYZER_HUNGARIAN => self::ANALYZER_HUNGARIAN,
		self::ANALYZER_INDONESIAN => self::ANALYZER_INDONESIAN,
		self::ANALYZER_IRISH => self::ANALYZER_IRISH,
		self::ANALYZER_ITALIAN => self::ANALYZER_ITALIAN,
		self::ANALYZER_LITHUANIAN => self::ANALYZER_LITHUANIAN,
		self::ANALYZER_NORWEGIAN=> self::ANALYZER_NORWEGIAN,
		self::ANALYZER_PERSIAN => self::ANALYZER_PERSIAN,
		self::ANALYZER_PORTUGUESE => self::ANALYZER_PORTUGUESE,
		self::ANALYZER_ROMANIAN => self::ANALYZER_ROMANIAN,
		self::ANALYZER_RUSSIAN => self::ANALYZER_RUSSIAN,
		self::ANALYZER_SORANI => self::ANALYZER_SORANI,
		self::ANALYZER_SPANISH => self::ANALYZER_SPANISH,
		self::ANALYZER_SWEDISH => self::ANALYZER_SWEDISH,
		self::ANALYZER_TURKISH => self::ANALYZER_TURKISH,
		self::ANALYZER_THAI => self::ANALYZER_THAI,
	];

	//
	// TOKENIZERS
	//

	public const TOKENIZER_STANDARD = 'standard';
	public const TOKENIZER_LETTER = 'letter';
	public const TOKENIZER_LOWERCASE = 'lowercase';
	public const TOKENIZER_WHITESPACE = 'whitespace';
	public const TOKENIZER_UAX_URL_EMAIL = 'uax_url_email';
	public const TOKENIZER_CLASSIC = 'classic';
	public const TOKENIZER_THAI = 'thai';

	public const TOKENIZER_NGRAM = 'ngram';
	public const TOKENIZER_EDGE_NGRAM = 'edge_ngram';

	public const TOKENIZER_KEYWORD = 'keyword';
	public const TOKENIZER_PATTERN = 'pattern';
	public const TOKENIZER_SIMPLE_PATTERN = 'simple_pattern';
	public const TOKENIZER_CHAR_GROUP = 'char_group';
	public const TOKENIZER_SIMPLE_PATTERN_SPLIT = 'simple_pattern_split';
	public const TOKENIZER_PATH = 'path_hierarchy';

	public const TOKENIZERS = [
		self::TOKENIZER_STANDARD 		=> self::TOKENIZER_STANDARD,
		self::TOKENIZER_LETTER 			=> self::TOKENIZER_LETTER,
		self::TOKENIZER_LOWERCASE 		=> self::TOKENIZER_LOWERCASE,
		self::TOKENIZER_WHITESPACE 		=> self::TOKENIZER_WHITESPACE,
		self::TOKENIZER_UAX_URL_EMAIL 	=> self::TOKENIZER_UAX_URL_EMAIL,
		self::TOKENIZER_CLASSIC 		=> self::TOKENIZER_CLASSIC,
		self::TOKENIZER_THAI 			=> self::TOKENIZER_THAI,

		self::TOKENIZER_NGRAM 		=> self::TOKENIZER_NGRAM,
		self::TOKENIZER_EDGE_NGRAM 	=> self::TOKENIZER_EDGE_NGRAM,

		self::TOKENIZER_KEYWORD 				=> self::TOKENIZER_KEYWORD,
		self::TOKENIZER_PATTERN 				=> self::TOKENIZER_PATTERN,
		self::TOKENIZER_SIMPLE_PATTERN 			=> self::TOKENIZER_SIMPLE_PATTERN,
		self::TOKENIZER_CHAR_GROUP 				=> self::TOKENIZER_CHAR_GROUP,
		self::TOKENIZER_SIMPLE_PATTERN_SPLIT 	=> self::TOKENIZER_SIMPLE_PATTERN_SPLIT,
		self::TOKENIZER_PATH 					=> self::TOKENIZER_PATH,
	];

	//
	// Setting blocks
	//
	public const BLOCK_TYPE = 'type';
	public const BLOCK_ANALYZER = 'analyzer';
	public const BLOCK_TOKENIZER = 'tokenizer';
	public const BLOCK_PROPERTIES = 'properties';

	public const BLOCKS = [
		self::BLOCK_TYPE 		=> self::BLOCK_TYPE,
		self::BLOCK_ANALYZER 	=> self::BLOCK_ANALYZER,
		self::BLOCK_TOKENIZER 	=> self::BLOCK_TOKENIZER,
		self::BLOCK_PROPERTIES 	=> self::BLOCK_PROPERTIES,
	];
}
