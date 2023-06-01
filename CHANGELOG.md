# Changelog

## [3.0.0] - 2023-05-31

### Added

- Add CHANGELOG file
- Add composer support
- Add unit tests
- Add support to access item's properties/fields with `$item->property` syntax
- Add support for default value on `Item::get()` method

### Changed

- **Breaking:** require PHP >= 8.1
- **Breaking:** change namespace from `ShoppingCart` to `Lombervid\ShoppingCart`
- Change license from GPL to MIT

### Removed

- **Breaking:** remove `coupon` property
- **Breaking:** remove `SymfonySessionStorage` storage

[Unreleased]: https://github.com/lombervid/shoppingcart/compare/v1.0...main
[3.0.0]: https://github.com/lombervid/shoppingcart/compare/v1.0...v3.0.0
