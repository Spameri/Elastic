.PHONY: tests
.PHONY: tests-local
.PHONY: phpstan
.PHONY: cs


tests:
	vendor/bin/tester -s -c ./tests/php.ini-unix ./tests

tests-local:
	vendor/bin/tester -j 1 -c ./tests/SpameriTests/php.ini tests

phpstan:
	vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests

cs:
	vendor/bin/phpcs --standard=vendor/spameri/coding-standard/src/ruleset.xml src tests/SpameriTests

cbf:
	vendor/bin/phpcbf --standard=vendor/spameri/coding-standard/src/ruleset.xml src tests/SpameriTests
