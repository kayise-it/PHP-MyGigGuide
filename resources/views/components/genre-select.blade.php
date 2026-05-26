@props([
    'name' => 'genre',
    'id' => 'genre',
    'value' => null,
    'placeholder' => 'Select a genre',
    'required' => false,
    'multiple' => false,
    'class' => '',
    'useNames' => false,
])

@php
    $genres = \App\Models\Genre::where('is_active', true)->orderBy('name')->get();
    $selectName = $multiple ? $name . '[]' : $name;
    $finalClasses = trim($siteBrand->formSelectClass() . ' ' . $class);
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
