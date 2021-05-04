.PHONY: tests
.PHONY: tests-local
.PHONY: phpstan
.PHONY: cs
.PHONY: cbf
.PHONY: cs-local
.PHONY: coverage
.PHONY: composer
.PHONY: composer-lowest


composer:
	composer update --no-interaction --no-suggest --no-progress --prefer-dist --prefer-stable

composer-lowest:
	composer update --no-interaction --no-suggest --no-progress --prefer-dist --prefer-stable --prefer-lowest

tests:
	vendor/bin/tester -s -c ./tests/php.ini-unix ./tests

tests-local:
	vendor/bin/tester -j 1 -c ./tests/SpameriTests/php.ini tests

phpstan:
	vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests

cs:
	vendor/bin/phpcs --standard=vendor/spameri/coding-standard/src/ruleset.xml --cache=.phpcs-cache src tests

cs-local:
	vendor/bin/phpcs --standard=vendor/spameri/coding-standard/src/ruleset.xml src tests/SpameriTests

cbf:
	vendor/bin/phpcbf --standard=vendor/spameri/coding-standard/src/ruleset.xml src tests/SpameriTests

coverage:
	vendor/bin/tester $COVERAGE -s -c ./tests/php.ini-unix ./tests
