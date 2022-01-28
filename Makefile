.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

qa: phpstan cs

cs:
	vendor/bin/phpcs -p -n --standard=phpcsRuleset.xml

csf:
	vendor/bin/phpcbf -p -n --standard=phpcsRuleset.xml

phpstan:
	vendor/bin/phpstan analyse src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

coverage-clover:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.xml --coverage-src ./src ./tests/Cases

coverage-html:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.html --coverage-src ./src ./tests/Cases
