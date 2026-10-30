# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `Format\EmailEscaper`: an email address as decimal entities (`text()`), and its mailto: link as entities and a
  percent-encoded address (`href()`), for page and email templates alike.

## [0.1.0] - 2026-10-23

### Added

- Office rows as the CMS's Office details saves them (`['fields' => [...]]`): empty values inherit the business's,
  and flat `latitude` and `longitude` become `geolocation`.
- `ContactSettings::fromSettings()`: the business under `contact.business` and its offices under `contact.office`,
  each office with the business's values unless it overrides them, or overrides them with none.
- Values written for the site's country: `Telephone` (national, international and `tel:`), `Email`, `TaxId`
  ("ABN 12 345 678 901"), `PostalAddress` and `Geolocation`, with map and directions links for OpenStreetMap or
  Google Maps.
- `OpeningHours`: entries of days and times in the office's timezone, with `summary()`, `isOpenAt()` and schema.org's
  OpeningHoursSpecification.
- `BusinessType` from a bundled copy of the schema.org 30.1 vocabulary, with the grouped `BusinessTypes` Office
  details offers, `AdditionalTypes` matched to Wikidata, `TypeDetail` for the values only some types take, and
  `bin/generate-vocabulary.php` to update it.
- `Offices` and `OfficeChoice`, for templates and Contact blocks: the primary office, by id or slug, by region, and a
  block's chosen offices.
- Formats: the package's `full`, `address` and `compact`, a site's own from its config, and `HtmlRenderer` to draw
  one as plain markup.
