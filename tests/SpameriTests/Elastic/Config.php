<?php declare(strict_types = 1);

namespace SpameriTests\Elastic;

class Config
{

	public const INDEX = 'spameri_product';
	public const TYPE  = '_doc';
	public const HOST  = '127.0.0.1';
	public const PORT  = '9200';
	public const CONNECTION = self::HOST . ':' . self::PORT;

	public const INDEX_DUMP = 'spameri_product_dump';
	public const INDEX_RESTORE = 'spameri_product_restore';
	public const INDEX_MIGRATE = 'spameri_product_migrate';
	public const INDEX_MIGRATE_NEW = 'spameri_product_migrate_new';

}
