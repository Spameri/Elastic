.PHONY: tests
.PHONY: tests-local
.PHONY: phpstan
.PHONY: cs


tests:
	vendor/bin/tester tests

tests-local:
	vendor/bin/tester -c tests/SpameriTests/php.ini tests

phpstan:
	vendor/bin/phpstan analyse -l 7 -c phpstan.neon src tests

cs:
	vendor/bin/phpcs --standard=vendor/spameri/coding-standard/src/ruleset.xml src tests
