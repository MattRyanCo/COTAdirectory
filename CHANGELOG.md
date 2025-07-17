# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - 2025-07-14

#### NOTE: This release of the changelog generated in part by Github Copilot reviewing commit history. 

Prompt:
Provide a summary of commits on the repo @MattRyanCo/COTAdirectory and identify all the changes applied since 2025-05-30. Format the output in markdown separated into 'Changed', 'Added', 'Fixed' areas. 

### Changed
- Update favicon image ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/ead92bec781182190dbd8570962d668b9ba7f19b))_
- Major tweaking of print to accommodate spacing ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/373b39e5353e4e392c156afe959bd0353e9729a9))
- Remove unneeded class structure from print ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/f28549fb352be11f15b1eba7833971f0794a6fe8))
- Change function naming ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/4b9ae6e962775cf77b1dfe5cb61e1a4e7ab2bdc6))
- Rework reset db to relocate form and header output ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/bb7215f3641d2be14bfc48e634516b3220572511))
- Nav refactoring complete ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/65da595b9c89d5214a7dd44c4e389aaae2bb99c6))
- State as of 6.26.2025 am before refactor of nav ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/a80a62a464ea6a13afcc4375e809a501c248feb4))
- Tweak db connect to use constants ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/e89bf9243f2272c938cf1847b1a474f9f34cea4b))
- Streamline db connctions. Add db info query ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/b44b0672b49405d469660fefb1cd4966abcd9f63))
- Check upcoming dates from next sunday only ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/9f4541041ba94c77d51fb3da85604b3f8866c064))
- Beginning of logic to adjust db connection depending on environment ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/770ec498f931e2ab2503084d7ab33f756a7a3276))
- Comment out debug code ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/490f1f2610e2cd78a021d0ebb0207b9822ea8ecf))
- Remove duplicate file from root ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/eaed19063072a52730eafcdc74fb1211d3ebd1a3))
- Updates to align navigation across pages ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/144fc64d89db62dbc9f487768c85d597b166706e))
- Create the booklet format output ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/15156663d61baf927239cce0f068526dfb5b485e))
- Refactoring to create global for db access and includes

### Added
- Add in helper function to delete logic ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/c28448d88fafbf923fff8d5c8365eb7e3fd4043f))
- Inlcude helper functions to get date formatters ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/b70096c1f964467fccf2fa8953e2b293db8f84aa))
- Add error message functions to helpers ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/2c6fe0ad703ef254ce25e78c06a61b5d71452cf6))
- Add logic to set db parms based on environment ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/9b63050543dacf17de4c862015637ab8e7c4441f))
- Add nav item to display some info about database ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/8d50d78eb3c89a4580fb14736efcd0eeca07a89c))
- Add login to handle name deletes ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/ef2e94a529a28f0150132cc5fa61f12d58cdaafa))

### Fixed
- Remove comment code ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/c566bb477354666605160d169a370f86b38db6d3))
- Fix styling of container ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/362050f399de02dad8998939f818f93307b94cf4))
- Ignore log files ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/81efc55f77b81bc0e9791ad9ae7cdea7c510aa6f))
- Correct issues with footer printing incorrectly ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/8158c849e79b6d78a2217ff5c35a2fad8db1636a))
- Remove commented out/unneeded code ([commit](https://github.com/MattRyanCo/COTAdirectory/commit/0cb38aa576c30588fc84fc51a6ce27581f74b482))
---



For more details, see the [full commit history](https://github.com/MattRyanCo/COTAdirectory/commits?since=2025-05-30).

### Changed

- Reworked include structure to remove redundancy
- Moved database handling to its own class
- modularized main page heading 

## [2.0.0] - 2025-05-30

### Changed

- Applied WP naming conventions to files, functions, css classes. 

## [1.1.1] - 2025-05-23

### Added

- Add Date of Baptism metadata for all members - DoBap
- Added encoding function to ensure special characters in phone numages and email address display correctly. 
- Added dynamic column heading for data export permitting up to specified number of family members. 
- Add maxFamilyMembers constant in class-COTA_Family_Directory_App.php to define max size of members db entry. 

### Changed

- Completed development of Display function with styling.
- Revise CSV export to one line per family

## [1.0.0] - 2025-0513

### Added

- Initial commit of system
- Import CSV Data
- Export Directory as CSV
- Display Formatted Directory
- Print Formatted Directory
- Search & Edit Family
- Add New Family
- Reset Database

### Changed
### Depricated
### Removed
### Fixed
### Security
