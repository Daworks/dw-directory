# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DW Directory is a WordPress plugin that provides directory/listing services. It enables sites to display categorized business listings (like local restaurants, church member businesses, etc.) with a hierarchical category system, user submissions with admin approval workflow, and search functionality.

**Shortcode**: `[dw-directory]` - Place this on any page/post to display the directory

**GitHub**: https://github.com/Daworks/dw-directory
**Docs**: https://daworks.github.io/dw-directory/

## Build Commands

```bash
# Install dependencies (first time setup)
npm install

# Compile SCSS to CSS (one-time)
gulp sass

# Watch for SCSS changes during development
gulp sass:watch
```

**Note**: The gulpfile references `./public/sass/` and `./admin/sass/` directories. Ensure these paths match your actual SCSS file locations (some setups use `scss` instead of `sass`).

## Architecture

### Plugin Structure (when full source is present)

```
dw-directory/
├── daworks.php              # Main plugin entry point
├── includes/                # Core plugin logic
│   ├── class-daworks.php           # Main orchestrator class
│   ├── class-daworks-loader.php    # WordPress hook registry
│   ├── class-daworks-activator.php # DB table creation on activation
│   └── functions-daworks.php       # AJAX handlers
├── admin/                   # Dashboard admin interface
│   ├── class-daworks-admin.php     # Admin menu & assets
│   ├── partials/                   # Admin page templates
│   └── js/css/                     # Admin assets
├── public/                  # Frontend display
│   ├── class-daworks-public.php    # Shortcode handler
│   ├── partials/                   # Public templates
│   └── js/css/                     # Frontend assets
└── languages/               # i18n translation files
```

### Key Design Patterns

- **Hook-based architecture**: `Daworks_Loader` class centralizes all WordPress action/filter registration
- **MVC-like separation**: Admin and public code are isolated in separate directories
- **AJAX-driven UI**: Most interactions use AJAX calls defined in `functions-daworks.php`

### Database Tables

The plugin creates two custom tables on activation:

1. **`wp_dw_directory`** - Directory items
   - `c_no` - Category ID
   - `ref`, `ref_n`, `lev` - Hierarchy references (0=root, 1=mid, 2=leaf)
   - `admin_ok` - Approval status (0=pending, 1=approved)

2. **`wp_dw_directory_category`** - Categories
   - `c_title` - Full path title using ">" delimiter (e.g., "식당 > 한식 > 서울")
   - `lev` - Hierarchy level (0, 1, or 2)

### Admin Menu Structure

- **디렉토리 서비스** (Directory Service) - Main menu
  - 등록 대기 관리 (Pending Items) - Approve/reject user submissions
  - 카테고리 관리 (Categories) - CRUD for category hierarchy
  - 아이템 관리 (Items) - Manage directory entries

### Key AJAX Actions

| Action | Purpose |
|--------|---------|
| `dw_apply_directory_item` | User submits new listing |
| `dw_grant_item` | Admin approves pending item |
| `dw_add_category` | Add new category |
| `dw_search_item` | Search directory |
| `dw_del_item` | Delete item |

## Development Notes

- Plugin text domain: `daworks`
- Requires WordPress 4.5+
- All strings use `__()` or `_e()` for i18n
- Uses jQuery for AJAX (WordPress bundled)
- Font Awesome for icons (bundled in public/bower_components/)
