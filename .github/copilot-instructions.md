# Project Guidelines

## Purpose
Concise guidance for AI coding agents working on the Fraggy Backend Theme (WBCE). Include only discoverable patterns and concrete commands agents can run locally.

## Code Style
- Languages: PHP (WBCE templates and backend scripts), SCSS, JavaScript.
- Follow existing repository patterns: procedural PHP in root scripts (e.g. `index.php`, `install.php`), and class-based code under `api/classes/Neoflow/` (see `api/classes/Neoflow/GitHubClient.php`).
- SCSS sources live in `src/sass/` and use the variables and mixins in `src/sass/_variables.scss` and `src/sass/_mixins.scss`.
- JavaScript: theme scripts live in `src/js/theme/`; vendor libs are in `src/vendor/`.

## Architecture
- This repository is a WBCE backend theme. Key areas:
  - Templates: `templates/` (files with `.htt` and some `.twig`). See `templates/header.htt` and `templates/footer.htt` for layout patterns.
  - Source assets: `src/` (SCSS, JS, source header). Built assets are placed into the repository root `css/` and `js/` for distribution.
  - Backend hooks and API endpoints: `api/`, plus installation/update scripts at the repo root (`install.php`, `update.php`, `upgrade.php`).

## Build and Test
- No automated PHP test suite detected. Manual verification on a local WBCE instance is expected for PHP changes.
- Node-based build pipeline (Gulp). Common commands (run from repo root):

```bash
npm install
npm run scss:build   # compile SCSS
npm run js:build     # build JS
npm run src:release  # build release artifacts (inject headers, zip, etc.)
npm run src:watch    # watch source files during development
```

- Gulp tasks are defined in `gulpfile.js` and `package.json` (see `scripts`). Built/distributed CSS and JS live in `css/` and `js/`.

## Project Conventions
- Source header: `src/source-header.txt` is injected into release files during `src:release`.
- When changing templates or styles, prefer editing `src/` files and run `npm run src:rebuild` or `npm run src:release` to regenerate distributables.
- Template files use `.htt` naming and use WBCE's backend templating conventions — do not convert these files without verifying WBCE compatibility.
- Custom assets referenced in README: `backend-theme-logo.png` and `backend-theme-favicon.png` should be placed in the theme root or the WBCE media folder.

## Integration Points
- This theme targets WBCE CMS (see README.md). Avoid breaking changes to `api/*` endpoints and installation scripts — these are integration points with the WBCE core.
- Frontend dependencies and build tooling are declared in `package.json` and `gulpfile.js`.

## Security and Sensitive Areas
- Review changes to `install.php`, `update.php`, `upgrade.php`, and `api/update.php` carefully — these scripts affect installation/update flows.
- Do not introduce secrets or credentials into the repository. If an integration needs credentials, document how to provide them via environment or runtime configuration outside source control.

## How AI Agents Should Contribute
- Prefer small, focused changes and include regenerated built assets when modifying styles or JS (run `npm run src:release`).
- When modifying PHP, run manual checks against a local WBCE instance; there are no automated PHP tests to run.
- Reference examples when following patterns: `templates/header.htt`, `src/sass/style.scss`, `api/classes/Neoflow/GitHubClient.php`.

## Quick Links
- Repository README: `README.md`
- Build config and scripts: `package.json`, `gulpfile.js`
- Source assets: `src/`
- Templates: `templates/`

If anything important is missing or unclear, please tell me which areas to expand or link examples for.
