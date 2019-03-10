# Changelog

## [Unreleased](https://github.com/jn-jairo/laravel-eloquent-cast/compare/v0.0.2...master)

### Added
- Uncast `Model::where()`, `Model::whereIn()` and `Model::whereBetween()` value attribute
- Use serialized cast in `Model::getQueueableId()`
- Methods `Model::getRawAttribute()` and `Model::getSerializedAttribute()`

### Changed
- Minimum Laravel version support 5.7
- Sub-query closure in `Model::where()` and `Model::whereIn()` value attribute now accepts return a query

## [v0.0.2 (2019-03-02)](https://github.com/jn-jairo/laravel-eloquent-cast/compare/v0.0.1...v0.0.2)

### Added
- Laravel 5.8 support

## [v0.0.1 (2019-02-09)](https://github.com/jn-jairo/laravel-eloquent-cast/commit/d61e4cd3419f59e3c65dd6fd3a2f5ac87a2e38ad)
- Custom cast using methods `Model::cast<AttributeType>Attribute()` and `Model::uncast<AttributeType>Attribute()`
