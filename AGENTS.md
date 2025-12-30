# CerneCMS Agent Instructions

You are an expert full-stack developer specializing in PHP and Svelte. Your mission is to maintain and extend the CerneCMS application, ensuring high-quality code and adherence to project conventions.

## Commands You Must Use

- **Start Backend Server**: `php -S 127.0.0.1:8080 -t public`
  - Serves the main application. Admin panel is at `http://localhost:8080/admin`.
- **Start Frontend Dev Server**: `pnpm run dev`
  - For frontend development with Hot Module Replacement (HMR). Access it at `http://localhost:5173/`.
- **Build Frontend Assets**: `pnpm run build`
  - **Run this after any frontend changes** to update the assets used by the PHP server.

## Project Knowledge

### Tech Stack

#### Back Office (Admin Panel)
*The interactive editor and management interface.*
- **Frontend**: Svelte 5 (with Vite)
- **State Management**: Svelte 5 Runes (`$state`, `$derived`).
- **Editor**: Tiptap for the block-based editor.
- **UI Library**: Flowbite Svelte components. See docs at `https://flowbite-svelte.com`.
  - **Icons**: NEVER guess icon names. Verify existence: `ls node_modules/flowbite-svelte-icons/dist/*.svelte | grep -i <IconName>`.
  - **⚠️ CRITICAL: Svelte 5 Syntax**: This project uses **Svelte 5**. You MUST follow Svelte 5 syntax:
    - **Event Handlers**: Use `onclick`, `oninput`, `onchange` (NOT `on:click`, `on:input`, `on:change`).
    - **State**: Use `$state()`, `$derived()`, `$effect()` runes (NOT `let` with `$:` reactive statements).
    - **Slots**: `svelte:fragment slot="name"` is still valid, but `{#snippet name}...{/snippet}` is the new Svelte 5 pattern.
    - **Binding**: `bind:value` syntax remains the same.

#### Public Site (Frontend)
*The actual website visited by end-users.*
- **Rendering Engine**: PHP (FlightPHP framework + Custom Renderers).
- **Styling**: TailwindCSS (compiled via the same build process).
- **Logic**: Minimal Vanilla JS (for interactivity like mobile menus or galleries).
- **Templates**: PHP files in `app/views/` or rendered blocks via `BlockRenderer.php`.

#### Core & Data
- **Language**: PHP 8.2+
- **Database**: SQLite. The database file is at `content/database/cms.sqlite`.
  - **Guide**: [Agent Guide: Database Interaction](docs/agent-guide-database.md) - ***Start here to avoid fatal errors!***
- **Package Manager**: pnpm

### File Structure
- `src/` – **Admin Svelte App** (Back Office). Compiled to `public/assets/`.
  - `src/App.svelte`: Main admin container.
  - `src/lib/components/Editor.svelte`: The core Tiptap editor component.
  - `src/lib/stores/editor.svelte.js`: Central state management for the editor.
- `app/` – **PHP Backend & Public Renderer**.
  - `app/controllers/ApiController.php`: REST API for the Admin App to talk to.
  - `app/services/BlockRenderer.php`: Converts block JSON (saved by Admin) to HTML (seen by public).
  - `app/models/`: ActiveRecord models.
- `public/` – Web server root.
- `content/` – User content (uploads, DB).

## Development Practices

### Adding a New Block Type
**See the detailed guide: [Agent Guide: Creating Blocks & Settings](docs/agent-guide-widget-settings.md)**

Overview of the process:
1.  **Backend**: Update `BlockRenderer.php` to render the JSON to HTML.
2.  **Admin (Tiptap)**: Create the usage extension and handling.
3.  **Admin (UI)**: Add the toolbar item and drag-and-drop logic.
4.  **Admin (Settings)**: Add configuration fields to the Settings Drawer.

### Testing
*No formal testing suite is currently configured.* Manually verify your changes in the browser. Pay close attention to console logs for both the browser and the PHP server terminal.

### Documenting New Features
Upon completion of a new feature, you are responsible for documenting its implementation details.

- **Location**: Create new markdown files in the `docs/` directory.
- **Content**: Describe the feature's architecture, key components, and any significant implementation details (e.g., how it integrates with existing systems, data flows, important code snippets).
- **Style**: Follow the structure and detail level of the [Agent Guide: Creating Blocks & Settings](docs/agent-guide-widget-settings.md).
- **Goal**: Ensure that another developer can understand how the feature is implemented by reading your documentation.

## Boundaries
- ✅ **Always do:** Run `pnpm run build` after making frontend changes. Follow the existing architectural patterns.
- ⚠️ **Ask first:** Before making breaking changes to the API in `ApiController.php` or altering the database schema.
- 🚫 **Never do:** Modify files in the `vendor/` or `node_modules/` directories. Never commit secrets or `.env` files.
