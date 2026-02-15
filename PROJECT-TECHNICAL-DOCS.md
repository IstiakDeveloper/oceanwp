# NGO Projects Module - Technical Documentation

## Overview
A complete project management system for NGO websites built on OceanWP WordPress theme. Features include custom post type, taxonomies, grid layout, filtering, and detailed project pages.

## File Structure

```
oceanwp/
├── inc/
│   └── ngo-projects.php              # Custom post type, taxonomies, meta boxes
├── archive-ngo_project.php           # Projects archive/listing page
├── single-ngo_project.php            # Single project detail page
└── assets/
    └── css/
        └── ngo-projects.css          # All styling for projects
```

## Custom Post Type

**Post Type:** `ngo_project`
**Slug:** `/projects/`
**Features:** Title, Editor, Thumbnail, Excerpt, Custom Fields

### Registration
Located in: `inc/ngo-projects.php`
Function: `oceanwp_register_projects_post_type()`

## Taxonomies

### 1. Project Status
- **Taxonomy:** `project_status`
- **Slug:** `project-status`
- **Type:** Hierarchical
- **Usage:** Filter ongoing vs completed projects
- **Default Terms:** Ongoing, Previous/Completed

### 2. Project Category
- **Taxonomy:** `project_category`
- **Slug:** `project-category`
- **Type:** Hierarchical
- **Usage:** Categorize by sector (Health, Education, etc.)

## Custom Meta Fields

All stored with underscore prefix for privacy:

| Field Name | Meta Key | Type | Description |
|------------|----------|------|-------------|
| Location | `_project_location` | Text | Geographic location |
| Duration | `_project_duration` | Text | Time period (e.g., "2024-2026") |
| Budget | `_project_budget` | Text | Project budget |
| Donor | `_project_donor` | Text | Funding organization |
| Goals | `_project_goals` | Textarea | Project objectives |
| Beneficiaries | `_project_beneficiaries` | Text | Target audience/numbers |
| Outcomes | `_project_outcomes` | Textarea | Line-separated outcomes |

### Meta Box Functions
- **Add:** `oceanwp_add_project_meta_boxes()`
- **Render:** `oceanwp_render_project_details_meta_box()`
- **Save:** `oceanwp_save_project_details_meta()`

## Templates

### Archive Template (`archive-ngo_project.php`)

**Features:**
- Page header with description
- Status filter buttons (All, Ongoing, Previous)
- Responsive grid layout
- Project cards with:
  - Featured image
  - Status badge
  - Title
  - Meta info (location, duration, budget, donor)
  - Excerpt
  - Read More button
- Pagination

**JavaScript:**
- jQuery filter functionality
- Fade in/out animations

### Single Template (`single-ngo_project.php`)

**Sections:**
1. **Hero Image** - Full-width featured image
2. **Header Section:**
   - Status badges
   - Project title
   - Categories
3. **Key Info Box** (Orange sidebar):
   - Location
   - Duration
   - Budget
   - Donor
   - Beneficiaries
4. **Content Sections:**
   - Project Overview (excerpt)
   - About the Project (content)
   - Project Goals
   - Key Outcomes (bulleted list)
5. **Navigation:**
   - Previous project
   - Back to all projects
   - Next project

## Styling

### Color Scheme
- **Primary Gradient:** #667eea → #764ba2 (Purple)
- **Secondary Gradient:** #ff6b35 → #f7931e (Orange)
- **Success:** #22c55e (Green)
- **Text:** #1e293b (Dark slate)
- **Muted:** #64748b (Slate)

### Responsive Breakpoints
- Desktop: 3 columns grid
- Tablet (≤991px): 2 columns
- Mobile (≤767px): 1 column

### Key Classes

**Archive Page:**
- `.projects-grid` - Main grid container
- `.project-card` - Individual card
- `.project-status-{slug}` - Dynamic status classes
- `.filter-btn` - Filter buttons
- `.status-badge` - Status labels

**Single Page:**
- `.single-project` - Main container
- `.project-header-section` - Top section with info box
- `.project-key-info` - Orange sidebar
- `.project-content-section` - Main content
- `.project-navigation` - Prev/next links

## Hooks and Filters

### Actions
- `init` - Register post type and taxonomies
- `add_meta_boxes` - Add custom meta boxes
- `save_post_ngo_project` - Save meta data
- `wp_enqueue_scripts` - Load CSS

### Available for Customization
You can hook into:
- `ocean_before_content` - Above projects grid
- `ocean_after_content` - Below projects grid
- `ocean_before_primary` - Before main content
- `ocean_after_primary` - After main content

## Database Structure

### Post Meta
All stored in `wp_postmeta` table:
```sql
post_id | meta_key              | meta_value
--------|----------------------|-------------
123     | _project_location     | "Dhaka, Bangladesh"
123     | _project_duration     | "April 2024 - March 2026"
123     | _project_budget       | "$100,000"
...
```

### Term Relationships
Standard WordPress taxonomy tables:
- `wp_terms` - Term names
- `wp_term_taxonomy` - Taxonomy info
- `wp_term_relationships` - Post-term links

## JavaScript

### Filter Functionality
Location: Inline script in `archive-ngo_project.php`

```javascript
jQuery('.filter-btn').on('click', function() {
    var status = $(this).data('status');
    if (status === 'all') {
        $('.project-card').fadeIn(300);
    } else {
        $('.project-card').hide();
        $('.project-status-' + status).fadeIn(300);
    }
});
```

## Setup Instructions

### 1. Flush Permalinks
After activating, go to: **Settings → Permalinks → Save Changes**

### 2. Create Default Terms
Navigate to: **Projects → Project Status**
Add:
- Ongoing (slug: ongoing)
- Previous (slug: previous)

### 3. Add to Menu
- Go to: **Appearance → Menus**
- Add "Projects" archive link

## Customization Guide

### Change Colors
Edit: `assets/css/ngo-projects.css`

**Header gradient:**
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**Info box:**
```css
background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
```

### Add New Status
1. Navigate to **Projects → Project Status**
2. Add new term (e.g., "Upcoming")
3. CSS class auto-generated: `.status-upcoming`
4. Add custom styling if needed

### Modify Grid Columns
Edit in `ngo-projects.css`:
```css
.projects-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
}
```

### Add New Meta Fields
1. Add field in `oceanwp_render_project_details_meta_box()`
2. Add to save array in `oceanwp_save_project_details_meta()`
3. Display in `single-ngo_project.php`

## Performance Considerations

- CSS minification recommended for production
- Images should be optimized (WebP format)
- Consider lazy loading for project images
- Archive pagination limits to default posts_per_page

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- IE11: Basic support (no grid, uses flexbox fallback)

## Accessibility

- Semantic HTML5 elements
- ARIA labels where needed
- Keyboard navigation support
- Focus states on interactive elements
- Alt text required for images

## Future Enhancements

Potential additions:
- Ajax filtering (no page reload)
- Project search
- Related projects
- Project timeline visualization
- Team members per project
- Download resources (PDFs, reports)
- Social sharing buttons
- Project location map integration

## Troubleshooting

**Projects page shows 404:**
- Flush permalinks: Settings → Permalinks → Save

**Styles not loading:**
- Check file path in `oceanwp_enqueue_projects_assets()`
- Clear browser cache

**Meta data not saving:**
- Check user capabilities
- Verify nonce validation
- Check for JavaScript errors

**Filter not working:**
- Ensure jQuery is loaded
- Check browser console for errors
- Verify status taxonomy terms exist

## Support

For issues or customization requests:
- Check `PROJECT-SETUP-GUIDE-BANGLA.md` for user guide
- Check `QUICK-START-BANGLA.md` for quick setup
- Review this technical documentation
- Test on staging environment before production

---

**Version:** 1.0.0
**Last Updated:** February 2026
**Compatibility:** WordPress 5.0+, OceanWP Theme
