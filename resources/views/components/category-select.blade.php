@props([
    'name' => 'categories',
    'id' => 'categories',
    'value' => null,
    'placeholder' => 'Select categories',
    'required' => false,
    'multiple' => true,
    'class' => '',
    'useIds' => true, // If false, uses category slugs as values
])

@php
    // Fetch all active categories from the database
    $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    
    // Determine if this is a multiple select
    $selectName = $multiple ? $name . '[]' : $name;
    
    // Base classes for the select element
    $baseClasses = 'px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent';
    $finalClasses = trim($baseClasses . ' ' . $class);
@endphp

<select 
    name="{{ $selectName }}" 
    id="{{ $id }}" 
    {{ $required ? 'required' : '' }}
    {{ $multiple ? 'multiple' : '' }}
    class="{{ $finalClasses }}"
    {{ $attributes }}
>
    @if(!$multiple)
        <option value="">{{ $placeholder }}</option>
    @endif
    
    @foreach($categories as $category)
        @php
            $optionValue = $useIds ? $category->id : $category->slug;
            $isSelected = false;
            
            if ($multiple && is_array($value)) {
                $isSelected = in_array($optionValue, $value) || in_array($category->id, $value) || in_array($category->slug, $value);
            } elseif (!$multiple) {
                $isSelected = ($value == $optionValue) || ($value == $category->id) || ($value == $category->slug);
            }
        @endphp
        <option 
            value="{{ $optionValue }}"
            {{ $isSelected ? 'selected' : '' }}
            data-color="{{ $category->color }}"
            data-icon="{{ $category->icon }}"
        >
            {{ $category->name }}
        </option>
    @endforeach
</select>




