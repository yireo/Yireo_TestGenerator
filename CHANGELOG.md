# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.6] - 26 November 2026
### Added
- PHP 8.5 support
- OpenAI model and Anthropic model settings to choose the AI model, replacing a hard-coded model that has been retired

### Changed
- AI test generators only run when the `--ai` flag is passed on top of the configuration toggle, so source code is no longer sent to an AI provider unintentionally
- The command refuses to write tests in production mode unless `--force` is given

### Removed
- PHP 8.1 support

### Fixed
- OpenAI and Anthropic API keys were stored in plain text; they are now encrypted (existing keys via a data patch), excluded from `app:config:dump` and limited to the default scope
- AI generated tests were written to disk unchecked; they are now syntax-checked and must contain the expected namespace and test class
- AI requests could hang or fail silently; they now time out and failed or empty responses skip the class with an error
- Module names from `etc/module.xml` could inject code into the generated ModuleTest; a missing sequence or unreadable module.xml is handled as well
- The className argument accepted classes outside of the given module
- `--override-existing=false` still overrode existing tests; boolean options are now parsed strictly and invalid values rejected
- Integration tests and the module test ignored the `@test-generator-skip-override` marker
- Generating unit tests for a single class also generated tests for every other class of the module
- The command failed when the area code was already set
- Installation conflicted with openai-php/client v0.22 (Mage-OS) and missed dependencies on Magento_Backend, Magento_Config and Magento_Store

## [0.0.5] - 22 October 2025
### Fixed
- Fix wrong import of Console InputArgument, should be Symfony clas
- Fix issue with classes that require appState
- Add PHP 8.4 support
- Add proxy to generators to prevent weird issues from breaking the CLI
- Major rewrite for reusage of method code
- Fix generator instantiation
- Move generators into separate listing class
- Add Anthropic / Claude AI support
- Add enabled flag for OpenAI
- Add OpenAI ChatGPT code generator for unit tests
- Fix ConfigFixture arguments
- Refactoring

## [0.0.4] - 24 March 2025
- Add generators for OpenAI and ClaudeAI (it works, but mwoah)
- Major refactoring of namespace structure
- Add cross-class `AdditionalTestGenerator` (like routes, modules, page behaviour) 
- Generate test methods for each accessible method of target class

## [0.0.3] - 17 March 2025
- Allow for other generators to be created

## [0.0.2] - 12 September 2024
- Refinements

## [0.0.1] - 15 August 2024
### Added
- Initial release
