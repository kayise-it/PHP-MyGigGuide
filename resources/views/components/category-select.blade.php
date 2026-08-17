@props([
    'name' => 'categories',
    'id' => 'categories',
    'value' => null,
    'placeholder' => 'Select categories',
    'required' => false,
    'multiple' => true,
    'class' => '',
    'useIds' => true,
])

@php
    $categories = \App\Models\Category::where('is_active', true)->orderedForDisplay()->get();
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
