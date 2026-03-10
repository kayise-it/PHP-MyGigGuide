@props([
    'name' => 'genre',
    'id' => 'genre',
    'value' => null,
    'placeholder' => 'Select a genre',
    'required' => false,
    'multiple' => false,
    'class' => '',
    'useNames' => false, // If true, uses genre names as values instead of IDs (for backward compatibility)
])

@php
    // Fetch all active genres from the database
    $genres = \App\Models\Genre::where('is_active', true)->orderBy('name')->get();
    
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
    
    @foreach($genres as $genre)
        @php
            $optionValue = $useNames ? strtolower($genre->slug) : $genre->id;
            $isSelected = false;
            
            if ($multiple && is_array($value)) {
                $isSelected = in_array($optionValue, $value) || in_array($genre->id, $value) || in_array($genre->name, $value);
            } elseif (!$multiple) {
                $isSelected = ($value == $optionValue) || ($value == $genre->id) || (strtolower($value) == strtolower($genre->name));
            }
        @endphp
        <option 
            value="{{ $optionValue }}"
            {{ $isSelected ? 'selected' : '' }}
        >
            {{ $genre->name }}
        </option>
    @endforeach
</select>

