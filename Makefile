.PHONY: shell

DOCKER := docker run --rm -it -w /app -v $(shell pwd):/app php:8.4-cli

shell:
	${DOCKER} bash

playground:
	${DOCKER} vendor/bin/phpunit tests/Functional/AppStoreReceiptVerificationTest.php