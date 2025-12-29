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
- **Preferred Package Manager**: pnpm
- **Backend**: PHP (FlightPHP framework)
- **Frontend**: Svelte 5 (with Vite)
- **Editor**: Tiptap for the block-based editor.
- **UI Library**: Flowbite Svelte components. See docs at `https://flowbite-svelte.com`.
  - **Icons**: NEVER guess icon names. They often differ from standard naming (e.g., `Angle` vs `Arrow` or `Chevron`). NOT all icons have both Solid and Outline variants (e.g., `PlusOutline` exists, but `PlusSolid` DOES NOT). ALWAYS verify the specific icon existence by running `ls node_modules/flowbite-svelte-icons/dist | grep -i <IconName>` before using it.
- **Database**: SQLite. The database file is at `content/database/cms.sqlite`.

### File Structure
- `src/` – Svelte frontend source code (You WRITE here).
  - `src/App.svelte`: Main admin container.
  - `src/lib/components/Editor.svelte`: The core Tiptap editor component.
  - `src/lib/stores/editor.svelte.js`: Central state management for the editor.
- `app/` – PHP backend source code (You WRITE here).
  - `app/controllers/ApiController.php`: REST API endpoints.
  - `app/services/BlockRenderer.php`: Converts block JSON to HTML.
  - `app/models/`: ActiveRecord models for database tables.
- `public/` – Web server root. Contains the main `index.php` and compiled assets.
- `content/` – User-generated content, uploads, and the database.

## Development Practices

### Adding a New Block Type (Example Workflow)
This is a good example of the development process.

1.  **Backend**: In `app/services/BlockRenderer.php`, add a new case to handle rendering the block's JSON to HTML on the frontend.
2.  **Frontend**: In `src/lib/stores/editor.svelte.js`, define a new Tiptap extension for the block.
3.  **Frontend**: In `src/lib/components/ComponentToolbar.svelte`, add a new icon/button to allow users to add the block via drag-and-drop.
4.  **Frontend**: Update the `handleDrop` logic in `src/lib/stores/editor.svelte.js` to correctly insert the new block into the editor state.

### Testing
*No formal testing suite is currently configured.* Manually verify your changes in the browser. Pay close attention to console logs for both the browser and the PHP server terminal.

### Documenting New Features
Upon completion of a new feature, you are responsible for documenting its implementation details.

- **Location**: Create new markdown files in the `docs/` directory.
- **Content**: Describe the feature's architecture, key components, and any significant implementation details (e.g., how it integrates with existing systems, data flows, important code snippets).
- **Style**: Follow the structure and detail level of the "Adding a New Block Type (Example Workflow)" in this document.
- **Goal**: Ensure that another developer can understand how the feature is implemented by reading your documentation.

## Boundaries
- ✅ **Always do:** Run `pnpm run build` after making frontend changes. Follow the existing architectural patterns.
- ⚠️ **Ask first:** Before making breaking changes to the API in `ApiController.php` or altering the database schema.
- 🚫 **Never do:** Modify files in the `vendor/` or `node_modules/` directories. Never commit secrets or `.env` files.
