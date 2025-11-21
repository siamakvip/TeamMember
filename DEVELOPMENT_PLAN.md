# WordPress Plugin Development Plan: Team Members

## Plugin Overview
A WordPress plugin that creates a custom post type for team members with associated taxonomy and a shortcode for displaying team members based on department and postal code input.

## Core Features

### 1. Custom Post Type: Team Members
- **Post Type Name**: `team_members`
- **Labels**: All in English
- **Fields Required**:
  - Name (Title)
  - Position (Subtitle/Job Title)
  - Organizational Position (Hierarchical placement)
  - Email Address
  - LinkedIn Profile URL
  - Description (Content/Excerpt)
  - Photo (Featured Image/Custom Field)

### 2. Custom Taxonomy: Department
- **Taxonomy Name**: `department`
- **Associated With**: `team_members` post type only
- **Type**: Hierarchical (like categories)
- **Labels**: All in English

### 3. Shortcode Implementation
- **Shortcode**: `[teammember departmentid="2" count="1"]`
- **Parameters**:
  - `departmentid`: Department taxonomy ID
  - `count`: Number of team members to display
- **Functionality**:
  - Displays a textbox with label "Enter your postal code to locate your advisor"
  - Accepts 10-digit postal codes only
  - On submission, randomly selects specified count of advisors from the department
  - Displays results in an organized container

## Technical Implementation Plan

### File Structure
```
team-members/
├── team-members.php (Main plugin file)
├── includes/
│   ├── class-post-type.php
│   ├── class-taxonomy.php
│   ├── class-shortcode.php
│   └── class-assets.php
├── templates/
│   └── shortcode-template.php
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── script.js
```

### Main Plugin File (`team-members.php`)
- Plugin header with name, description, version
- Activation hook for registering post types and taxonomies
- Deactivation hook cleanup
- Include required classes

### Post Type Class (`includes/class-post-type.php`)
- Register `team_members` post type
- Add custom fields support
- Create meta boxes for additional fields (position, email, etc.)
- Save post meta functionality

### Taxonomy Class (`includes/class-taxonomy.php`)
- Register `department` taxonomy
- Associate with `team_members` post type
- Add department-specific fields if needed

### Shortcode Class (`includes/class-shortcode.php`)
- Register shortcode `[teammember]`
- Process shortcode attributes
- Handle AJAX request for postal code validation
- Generate random team members based on department
- Return formatted HTML output

### Assets Class (`includes/class-assets.php`)
- Enqueue frontend CSS and JavaScript
- Localize JavaScript for AJAX calls
- Enqueue admin CSS for custom post type UI

### Template File (`templates/shortcode-template.php`)
- HTML structure for the postal code input
- Results container for displaying team members
- Form validation and submission handling

### JavaScript (`assets/js/script.js`)
- Validate 10-digit postal code input
- Handle form submission via AJAX
- Display results in the container

### CSS (`assets/css/style.css`)
- Style for the postal code input form
- Layout for displaying team member results
- Responsive design considerations

## Database Considerations
- No additional database tables needed
- Will use WordPress posts, postmeta, and term relationships
- Team members stored as posts with department taxonomy terms

## Security Considerations
- Sanitize and validate all input data
- Nonce verification for AJAX requests
- Proper escaping of output data
- SQL injection prevention through WordPress functions

## Internationalization
- All text strings properly wrapped for translation
- English as default language
- Ready for localization if needed in the future

## Questions for Clarification

1. **Postal Code Format**: When you say "10-digit postal code", do you mean exactly 10 digits (like in some countries) or should it be flexible for different postal code formats?

2. **Random Selection**: When you say "randomly selected", do you want truly random selection each time, or should the same postal code always return the same advisors for consistency?

3. **Advisor vs Team Member**: In the description you mention both "team members" and "advisors" - are these the same thing or different roles?

4. **Display Format**: How should the team member results be displayed? Just name and photo or include other fields like position, email, etc.?

5. **Error Handling**: What should happen if no team members are found for a department or postal code?

6. **Admin Interface**: Do you need any special admin features like bulk import/export of team members?

7. **Caching**: Should the results be cached to improve performance?

8. **Multiple Departments**: Can a single team member belong to multiple departments?