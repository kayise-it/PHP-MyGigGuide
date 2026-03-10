# Genre Select Component

A reusable Blade component for selecting music genres from the database.

## Features
- Automatically fetches all active genres from the database
- Supports both ID-based and name-based values (for backward compatibility)
- Supports single and multiple selection
- Fully customizable styling
- Works with Laravel validation and old input

## Basic Usage

### Simple Dropdown (Uses Genre IDs)
```blade
<x-genre-select />
```

### With Custom Attributes
```blade
<x-genre-select 
    id="artist_genre" 
    name="genre" 
    placeholder="Select your genre"
    required
/>
```

### Using Genre Names (for backward compatibility with text-based genre storage)
```blade
<x-genre-select 
    id="genre" 
    name="genre" 
    :value="old('genre', $artist->genre)" 
    required 
    use-names
/>
```

### Multiple Selection
```blade
<x-genre-select 
    id="genres" 
    name="genres" 
    :value="old('genres', [])" 
    multiple
/>
```

### With Custom Classes
```blade
<x-genre-select 
    class="w-full @error('genre') border-red-500 @enderror"
/>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | string | `'genre'` | The name attribute for the select element |
| `id` | string | `'genre'` | The id attribute for the select element |
| `value` | mixed | `null` | The selected value(s) - can be ID, name, or array for multiple |
| `placeholder` | string | `'Select a genre'` | The placeholder option text |
| `required` | boolean | `false` | Whether the field is required |
| `multiple` | boolean | `false` | Allow multiple genre selection |
| `class` | string | `''` | Additional CSS classes to apply |
| `use-names` | boolean | `false` | Use genre slugs as values instead of IDs (for backward compatibility) |

## Examples in Context

### In a Form with Validation
```blade
<div>
    <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">
        Genre <span class="text-red-500">*</span>
    </label>
    <x-genre-select 
        id="genre" 
        name="genre" 
        :value="old('genre', $artist->genre ?? '')" 
        required 
        use-names
        class="w-full @error('genre') border-red-500 @enderror" 
    />
    @error('genre')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
```

### In a Search Filter
```blade
<x-genre-select 
    id="genre_filter" 
    name="genre" 
    :value="request('genre')" 
    placeholder="All Genres"
    use-names
/>
```

### Multiple Genres for an Event
```blade
<x-genre-select 
    id="event_genres" 
    name="genres" 
    :value="old('genres', $event->genres->pluck('id')->toArray())" 
    multiple
    required
    class="w-full h-32"
/>
```

## JavaScript Integration

The component works seamlessly with JavaScript since it renders a standard `<select>` element:

```javascript
const genreSelect = document.getElementById('genre');
const selectedGenre = genreSelect.value;

// For multiple selection
const selectedGenres = Array.from(genreSelect.selectedOptions).map(option => option.value);
```

## Data Source

The component automatically fetches genres from the database using:
```php
\App\Models\Genre::where('is_active', true)->orderBy('name')->get()
```

Ensure your genres are properly set up in the admin panel at `/admin/genres`.

## Migration from Hardcoded Genres

If you're migrating from hardcoded genre lists, use `use-names` prop:

### Before (Hardcoded)
```blade
<select name="genre">
    <option value="rock">Rock</option>
    <option value="pop">Pop</option>
    <option value="jazz">Jazz</option>
</select>
```

### After (Component with backward compatibility)
```blade
<x-genre-select name="genre" use-names />
```

This will maintain the same slug-based values while pulling from the database.










