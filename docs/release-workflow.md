# Release Workflow Documentation

## Overview

CerneCMS uses an automated GitHub Actions workflow to create releases with semver versioning. When you push a version tag (e.g., `v1.0.0`, `v1.2.3`), the workflow automatically builds the project and creates a release zip file.

## Workflow File

The release workflow is defined in [`.github/workflows/release.yml`](../.github/workflows/release.yml).

## How It Works

### Trigger

The workflow is triggered when you push a git tag matching the pattern `v*.*.*` (semver format):

```bash
git tag v1.0.0
git push origin v1.0.0
```

### Build Process

The workflow performs the following steps:

1. **Checkout Code**: Retrieves the repository code at the tagged commit
2. **Setup PHP**: Installs PHP 8.2 with required extensions (pdo, pdo_sqlite)
3. **Install Composer Dependencies**: Runs `composer install --no-dev` to install production dependencies
   - The `vendor/` folder is included in the release zip
   - Development dependencies are excluded
4. **Setup Node.js**: Installs Node.js 18
5. **Install pnpm**: Sets up pnpm package manager
6. **Install Frontend Dependencies**: Runs `pnpm install --frozen-lockfile`
7. **Build Frontend Assets**: Runs `pnpm run build` to compile Svelte assets
   - Built assets are placed in `public/assets/`
8. **Create Release Directory**: Creates a temporary `release/` directory
9. **Copy Project Files**: Copies all necessary files and directories to the release directory
10. **Create Configuration Files**: Generates `.env.example` file
11. **Create Zip File**: Packages everything into `cernecms-{version}.zip`
12. **Create GitHub Release**: Creates a GitHub release with the zip file as an asset
13. **Upload Artifact**: Uploads the zip file as a workflow artifact (retained for 90 days)

## What's Included in the Release

The release zip file includes:

### Directories
- `app/` - PHP application code
- `bin/` - CLI tools (including the `cerne` command)
- `content/` - User-generated content directory (database, uploads, themes, plugins)
- `docs/` - Documentation
- `plans/` - Implementation plans
- `public/` - Web server root with built assets
- `src/` - Svelte frontend source code
- `tests/` - Test files
- `vendor/` - PHP dependencies (Composer packages)

### Root Files
- `.gitignore` - Git ignore rules
- `AGENTS.md` - Agent instructions
- `CHANGELOG.md` - Changelog
- `composer.json` - Composer configuration
- `composer.lock` - Composer lock file
- `docker-compose.yml` - Docker Compose configuration
- `Dockerfile` - Docker configuration
- `flight.php` - FlightPHP configuration
- `GEMINI.md` - Gemini AI instructions
- `INSTALL.md` - Installation guide
- `package.json` - npm configuration
- `pnpm-lock.yaml` - pnpm lock file
- `phpunit.xml` - PHPUnit configuration
- `README.md` - Project README
- `vite.config.js` - Vite configuration
- `vitest.config.js` - Vitest configuration

### Generated Files
- `.env.example` - Environment configuration template

## Creating a Release

### Step 1: Update Version Information

Before creating a release, ensure your version information is up to date:

1. Update the version in `composer.json` if needed
2. Update `CHANGELOG.md` with the changes in this release
3. Commit these changes

### Step 2: Create and Push a Tag

Create a semver tag and push it to GitHub:

```bash
# Create a new tag
git tag v1.0.0

# Push the tag to GitHub
git push origin v1.0.0
```

The workflow will automatically start and create the release.

### Step 3: Monitor the Workflow

1. Go to the "Actions" tab in your GitHub repository
2. Click on the "Release" workflow run
3. Monitor the progress of the build

### Step 4: Download the Release

Once the workflow completes:

1. Go to the "Releases" section of your GitHub repository
2. Find the release with your version tag
3. Download the `cernecms-{version}.zip` file

## Semver Versioning

The workflow follows Semantic Versioning (semver) format: `MAJOR.MINOR.PATCH`

- **MAJOR**: Incompatible API changes
- **MINOR**: Backwards-compatible functionality additions
- **PATCH**: Backwards-compatible bug fixes

Examples:
- `v1.0.0` - Initial release
- `v1.0.1` - Bug fix
- `v1.1.0` - New feature
- `v2.0.0` - Breaking changes

## Installation for End Users

End users can install CerneCMS by:

1. Downloading the latest release zip from the GitHub Releases page
2. Extracting the zip file to their web server directory
3. Copying `.env.example` to `.env` and configuring their settings
4. Setting up the database (SQLite is included in `content/database/`)
5. Accessing the admin panel at `/admin`

Detailed installation instructions are included in the `INSTALL.md` file within the release zip.

## Benefits

- **No Composer Required**: End users don't need to install Composer or run `composer install`
- **Pre-built Assets**: Frontend assets are already built and optimized
- **Complete Package**: Everything needed to run CerneCMS is included
- **Automated Process**: Releases are created automatically when you push a tag
- **Version Control**: Clear versioning with semver tags
- **Release Notes**: GitHub automatically generates release notes from commits

## Troubleshooting

### Workflow Fails

If the workflow fails:

1. Check the workflow logs in the Actions tab
2. Ensure all tests pass before creating a release
3. Verify that `composer.json` and `package.json` are valid
4. Make sure the tag follows semver format (`v*.*.*`)

### Release Not Created

If the release is not created:

1. Verify the tag was pushed correctly: `git ls-remote --tags origin`
2. Check that the workflow has the necessary permissions (`contents: write`)
3. Ensure the `GITHUB_TOKEN` is properly configured

### Zip File Issues

If the zip file has issues:

1. Verify all necessary files are copied in the "Copy project files" step
2. Check that the zip command completes successfully
3. Ensure the zip file is attached to the GitHub release

## Customization

### Modifying the Workflow

To customize the release workflow:

1. Edit [`.github/workflows/release.yml`](../.github/workflows/release.yml)
2. Test changes on a branch before merging to main
3. Consider adding additional steps like:
   - Running tests before creating the release
   - Creating checksums for the zip file
   - Publishing to a package registry
   - Sending notifications

### Changing Included Files

To change what files are included in the release:

1. Modify the "Copy project files" step in [`.github/workflows/release.yml`](../.github/workflows/release.yml)
2. Add or remove `cp` commands as needed
3. Update the documentation accordingly

### Updating Installation Instructions

To update the installation guide:

1. Edit [`INSTALL.md`](../INSTALL.md) in the project root
2. Commit and push the changes
3. The updated file will be included in the next release

## Related Files

- [`.github/workflows/release.yml`](../.github/workflows/release.yml) - Release workflow definition
- [`.github/workflows/test.yml`](../.github/workflows/test.yml) - Test workflow (runs on push to main/develop)
- [`composer.json`](../composer.json) - PHP dependencies
- [`package.json`](../package.json) - Frontend dependencies
- [`CHANGELOG.md`](../CHANGELOG.md) - Changelog
- [`INSTALL.md`](../INSTALL.md) - Installation guide for end users

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Semantic Versioning](https://semver.org/)
- [GitHub Releases](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [softprops/action-gh-release](https://github.com/softprops/action-gh-release) - Action used for creating releases
