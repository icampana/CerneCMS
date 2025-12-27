"CerneCMS" is a modern content management framework that combines the "fun coding experience" of ImpressPages with the architectural rigor of modern micro-services.

### 3.1 Core Technology Stack

The selection of the technology stack is driven by three principles: **Minimalism**, **Performance**, and **Developer Experience (DX)**.

|**Component**|**Technology**|**Rationale**|
|---|---|---|
|**Backend Framework**|**FlightPHP (v3)**|A fast, simple, extensible micro-framework. It offers routing, middleware, and dependency injection without the bloat of Laravel. It aligns perfectly with the goal of a lightweight, understandable core.23|
|**Data Storage**|**SQLite**|Provides a serverless, zero-configuration database engine. It supports JSON columns for dynamic content and handles high-read workloads efficiently via WAL mode.21|
|**Frontend Runtime**|**Svelte 5**|Selected for its compiled nature, which results in tiny bundle sizes. Its new "Runes" system offers fine-grained reactivity, essential for a high-performance inline editor.25|
|**Editor Engine**|**Tiptap**|A headless wrapper for ProseMirror. It provides the block-based JSON structure and programmatic control needed for slash commands and custom node views.27|

### 3.2 Backend Architecture (The Flight Engine)

The backend is designed to be a thin layer that orchestrates data flow between the SQLite database and the Svelte frontend.

#### 3.2.1 Directory Structure

The project structure emphasizes the separation of the core framework from user-generated content, facilitating easy updates.

/flight-cms
```
├── /app
│ ├── /config # Configuration files (Database, Routes, Services)
│ ├── /controllers # Logic for Admin API and Frontend Rendering
│ ├── /middleware # Auth, CSRF, Locale handling
│ ├── /models # Data Access Objects (SQLite wrappers)
│ ├── /services # Core logic: PluginManager, GridService, MediaService
│ └── /views # Server-side templates (Latte/Blade) for the app shell
├── /content
│ ├── /database # Stores the cms.sqlite file (Protected via.htaccess)
│ ├── /uploads # User-uploaded media assets
│ ├── /themes # User-installed themes
│ └── /plugins # User-installed plugins
├── /public # The Web Root
│ ├── /assets # Compiled JS/CSS (Svelte build artifacts)
│ └── index.php # The application entry point
├── /vendor # Composer dependencies
└── flight.php # The Framework Bootstrapper
```
#### 3.2.2 The Modular Plugin System

To replicate the extensibility of ImpressPages, FlightCMS utilizes an event-driven plugin architecture. FlightPHP’s extensibility features allow us to map methods and trigger events globally.28

Event System Design:

We define a set of core lifecycle events that plugins can subscribe to:

- `cms.boot`: Fired when the application starts. Used for registering services.
- `cms.router`: Fired before routing. Plugins can register custom routes (e.g., `/shop/cart`).
- `cms.admin.menu`: Fired when building the admin sidebar. Plugins add their own menu items.
- `cms.output`: Fired before sending the response. Used for injecting SEO meta tags or analytics scripts.


Implementation Detail:

Plugins are stored in /content/plugins/{PluginName}. The PluginManager service scans this directory for a boot.php file and executes it.

PHP

```
// app/services/PluginManager.php
class PluginManager {
    public function load() {
        $plugins = $this->db->fetch("SELECT name FROM plugins WHERE active = 1");
        foreach ($plugins as $plugin) {
            $path = PATH_CONTENT. "/plugins/". $plugin['name']. "/boot.php";
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
}

// content/plugins/Analytics/boot.php
Flight::on('cms.output', function(&$html) {
    $code = Flight::get('analytics_code');
    $html = str_replace('</body>', $code. '</body>', $html);
});
```

#### 3.2.3 Data Schema: The Hybrid Approach

We leverage SQLite's JSON support to create a flexible content model. This avoids the "Entity-Attribute-Value" (EAV) pattern, which is notoriously slow.

**Table: `pages`**

|**Column**|**Type**|**Description**|
|---|---|---|
|`id`|INTEGER PK|Primary Key|
|`slug`|TEXT|URL slug (indexed for fast lookups)|
|`title`|TEXT|Page title for `<title>` tags|
|`status`|TEXT|`published`, `draft`, `trash`|
|`layout`|TEXT|The theme template file to use (e.g., `home.php`)|
|`meta_json`|TEXT|JSON object for SEO data (OG tags, descriptions)|

Table: blocks (The Content Atom)

Instead of a single "body" column, content is stored as discrete blocks linked to a page.

|**Column**|**Type**|**Description**|
|---|---|---|
|`id`|INTEGER PK|Primary Key|
|`page_id`|INTEGER|Foreign Key to `pages`|
|`zone`|TEXT|The template region (e.g., `main`, `footer`, `sidebar`)|
|`type`|TEXT|Block type (e.g., `text`, `image`, `form`)|
|`content_json`|TEXT|**Tiptap JSON** structure storing the block data|
|`settings_json`|TEXT|Visual settings (alignment, background color)|
|`sort_order`|INTEGER|Positioning within the zone|

### 3.3 Frontend Architecture (The Editor)

The frontend is a Single Page Application (SPA) functionality embedded within the server-rendered theme.

#### 3.3.1 Svelte 5 and Tiptap Integration

The challenge with Svelte 5 is the introduction of "Runes," which changes how reactivity is handled.25 The standard Tiptap integration guides often rely on Svelte 4 stores, which break in the new paradigm.

The Solution: A Reactive Wrapper Component

We must build a wrapper that bridges Tiptap's event loop with Svelte's state runes.

1. **State Initialization**: Use `$state` to hold the editor instance.
2. **Transaction Listening**: Bind to Tiptap's `transaction` event. When the editor state changes (e.g., user types a character), we must manually trigger a state update in Svelte so that dependent UI components (like the bold button state) update.
3. **JSON Serialization**: Use `editor.getJSON()` to extract the structured data for saving to the backend.

#### 3.3.2 The Node View System

To replicate ImpressPages' widgets, we utilize Tiptap's **Node Views**.29 This allows us to render a Svelte component _inside_ the content editor.

- **Example: The "Form" Block**:

    - In the editor, this renders as a Svelte component with a UI for adding fields (`<input>`, `<select>`) and configuring email recipients.
    - The user drags fields to reorder them (using Svelte DnD).
    - The configuration is saved as a JSON object in the block's `attrs`.
    - On the frontend (published site), a PHP helper renders the actual HTML form based on this JSON.


#### 3.3.3 The Layout Engine (Grid)

We replace the Bootstrap grid with a CSS Grid implementation.

- **The "Section" Block**: This is a container block that holds other blocks.
- **Grid Controls**: The editor UI provides a slider or input to define the number of columns (1-12) and the gap size.
- **Responsive Overrides**: The user can toggle to "Mobile View" in the editor. Changes made to the grid configuration here (e.g., changing a 3-column layout to a 1-column stack) are saved into a separate `mobile_settings` object in the block's JSON.

---

## Part IV: Implementation Guide for the Development Team

This section serves as the "Runbook" for the engineering team, detailing the specific steps to bring FlightCMS to life.

### 4.1 Phase 1: Foundation and Backend Core (Weeks 1-4)

**Goal**: Establish the FlightPHP framework, SQLite connectivity, and the basic routing mesh.

1. **Environment Setup**:
    - Initialize the project using `composer create-project flightphp/skeleton`.
    - Install `latte/latte` for server-side templating (Themes).30
    - Install `vlucas/phpdotenv` for environment variable management.
    - Create the `content/database` directory and secure it with an `.htaccess` file (`Deny from all`).
2. **Database Abstraction Layer**:
    - Create a `Database` class extending `PDO`.
    - Implement a `SchemaManager` service that runs on boot. It should check for the existence of `cms.sqlite` and run migrations if tables are missing.31
    - **Task**: Implement the `pages` and `blocks` tables defined in Section 3.2.3.

3. **Routing and Controllers**:
    - Define the admin routes group (`/admin/*`) protected by an `AuthMiddleware`.
    - Implement `AdminController`: Handles the dashboard and page management views.
    - Implement `ApiController`: A REST API for the editor to save/load data (`GET /api/pages/{id}`, `POST /api/blocks/save`).
    - **Task**: Create a wildcard route (`/*`) that catches all other requests and dispatches them to the `FrontendController` for theme rendering.


### 4.2 Phase 2: The Editor Experience (Weeks 5-10)

**Goal**: Build the "Visual Editor" using Svelte 5 and Tiptap.

1. **Frontend Build Pipeline**:
    - Set up **Vite** with the `@sveltejs/vite-plugin-svelte` plugin.
    - Configure the build to output `editor.js` and `editor.css` to `/public/assets`.
    - **Task**: Ensure the Svelte app can be mounted onto a DOM element ID (`#cms-editor`) present in the admin view.

2. **The Editor Component**:
    - Implement the Tiptap wrapper using Svelte Runes.
    - **Slash Menu**: Create a floating component that triggers on `/`. It should list available blocks.
    - **Bubble Menu**: Create a floating toolbar that appears on text selection (Bold, Italic, Link).

3. **Core Blocks Implementation**:
    - **Text Block**: Standard paragraph handling.
    - **Image Block**: A Node View that handles file uploads. It should POST to `/api/media/upload` and receive a URL.
    - **Grid Block**: A container block that renders a `div` with `display: grid`. It must allow dropping other blocks inside it (Nested Tiptap instances or specific drop zones).

### 4.3 Phase 3: The Theme Engine & Rendering (Weeks 11-13)

**Goal**: Render the stored JSON content into HTML for the visitor.

1. **The Renderer Service**:
    - Create a PHP class `BlockRenderer`.
    - It accepts the Tiptap JSON structure.
    - It iterates through the blocks and dispatches them to specific view files (e.g., `themes/default/blocks/image.latte`).
    - **Task**: This allows themes to completely control the HTML output of standard blocks.

2. **Theme Structure**:
    - Define the standard theme file: `layout.latte`.
    - Define variable injection: The `FrontendController` injects `$content` (the rendered HTML of all blocks) and `$meta` (SEO tags) into the template.

### 4.4 Phase 4: Modern Plugin Replacements (Weeks 14-16)

**Goal**: Rebuild the essential ImpressPages functionalities as FlightCMS plugins.

1. **The "DataGrid" Plugin**:
    - **Backend**: A `GridController` that accepts a table name. It uses `PRAGMA table_info` to inspect the SQLite schema.
    - **Frontend**: A generic Svelte component that renders a table based on the schema. It supports sorting and pagination via API query parameters.
    - **Requirement**: This replaces `ipGrid` and allows developers to build custom admin panels quickly.32

2. **The "Forms" Plugin**:
    - **Editor**: A Svelte Node View allowing drag-and-drop construction of forms.
    - **Backend**: A route `POST /api/form/submit` that handles validation and emailing.
    - **Storage**: Save form submissions to a `form_entries` table in SQLite for export.

3. **The "Media" Plugin**:
    - A central repository view in the admin.
    - Features: Drag-and-drop upload, WebP conversion (using PHP GD), and a file picker modal that can be invoked by other plugins.


## Conclusion

The proposed "FlightCMS" architecture is not merely a modernization; it is a strategic alignment of the "ImpressPages Spirit" with the realities of the contemporary web. By leveraging **FlightPHP**, we maintain the lightweight, understandable code that developers loved. By adopting **SQLite**, we recapture the portability of flat-file systems. And by utilizing **Svelte 5** and **Tiptap**, we deliver an editing experience that rivals the best SaaS platforms like Notion and Squarespace. This roadmap provides a clear path for a development team to build a tool that is robust, delightful, and future-proof.
