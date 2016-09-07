#!/bin/sh

echo "Setting up Git pre-commit hook."
cp scripts/pre-commit.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

echo "Setting up Code Sniffer."
vendor/bin/phpcs --config-set installed_paths ../../drupal/coder/coder_sniffer
vendor/bin/phpcs --config-set show_progress 1
