# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
