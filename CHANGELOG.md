# Change Log

## Unreleased
### Fixed
- Cache DBs per-site ([#26])
- Drop all tables before importing DB ([#24]).

## 0.2.0 - 2017-10-18
### Added
- Add a `--skip-check` option to prevent cms-builder requesting the front page after a build.

## 0.1.0 - 2017-06-23
### Changed
- Update platform-docker so that the Platform.sh php.ini is used by the Docker PHP container.

[#24]: https://github.com/tes/cms-builder/issues/24
[#26]: https://github.com/tes/cms-builder/issues/26