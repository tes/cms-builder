# Change Log

## 0.4.0 - 2018-01-21
### Added
- ([platform-docker]) Added a PHP 7.1 Docker file.

## 0.3.0 - 2017-11-21
### Changed
- ([platform-docker]) Change nginx `client_max_body_size` to default to 50MB ([#29]).
### Added
- ([platform-docker]) nginx will read a config file in the project root called `nginx.conf` if available ([#29]).

## 0.2.1 - 2017-11-13
### Fixed
- Cache DBs per-site ([#26])
- Drop all tables before importing DB ([#24]).

## 0.2.0 - 2017-10-18
### Added
- Add a `--skip-check` option to prevent cms-builder requesting the front page after a build.

## 0.1.0 - 2017-06-23
### Changed
- Update [platform-docker] so that the Platform.sh php.ini is used by the Docker PHP container.

[#24]: https://github.com/tes/cms-builder/issues/24
[#26]: https://github.com/tes/cms-builder/issues/26
[#29]: https://github.com/tes/cms-builder/issues/29
[platform-docker]: https://github.com/tes/platform-docker
