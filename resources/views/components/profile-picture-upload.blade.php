@props([
    'name' => 'profile_picture',
    'id' => 'profile_picture',
    'currentImage' => null,
    'showCurrent' => true,
    'maxSize' => '10MB',
    'required' => false,
    'class' => '',
    'previewSize' => 'h-32 w-32', // Size for current image preview
])

@php
    $hasCurrentImage = false;
    if ($currentImage) {
        try {
            $hasCurrentImage = \Illuminate\Support\Facades\Storage::disk('public')->exists($currentImage);
        } catch (\Exception $e) {
            $hasCurrentImage = false;
        }
    }
    $baseClasses = 'w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent';
    $finalClasses = trim($baseClasses . ' ' . $class);
    $functionId = str_replace('-', '_', $id) . '_' . uniqid();
@endphp

<div class="space-y-4">
    @if($showCurrent && $hasCurrentImage && $currentImage)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Profile Picture</label>
            <div class="flex items-center space-x-4">
                <img 
                    src="{{ \Illuminate\Support\Facades\Storage::url($currentImage) }}" 
                    alt="Current Profile Picture" 
                    id="current-{{ $id }}-preview"
                    class="{{ $previewSize }} object-cover rounded-full border-4 border-gray-200"
                    onerror="this.style.display='none'; this.parentElement.parentElement.style.display='none';"
                >
                <div>
                    <p class="text-sm text-gray-600">Current profile picture</p>
                    <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                </div>
            </div>
        </div>
    @elseif($showCurrent && !$hasCurrentImage && $currentImage)
        <div class="text-sm text-gray-500 mb-4">
            <p>Profile picture path exists but file not found. Upload a new image.</p>
        </div>
    @endif

    <div>
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-2">
            Profile Picture @if($required) <span class="text-red-500">*</span> @endif
        </label>
        
        <div class="mb-2">
            <img 
                id="{{ $id }}-preview"
                alt="Preview" 
                class="{{ $previewSize }} object-cover rounded-full border-4 border-gray-200 hidden"
            >
        </div>

        <input 
            type="file" 
            id="{{ $id }}" 
            name="{{ $name }}" 
            accept="image/*"
            {{ $required ? 'required' : '' }}
            onchange="previewImage_{{ $functionId }}(this)"
            class="{{ $finalClasses }} @error($name) border-red-500 @enderror"
            {{ $attributes }}
        >
        
        @error($name)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        
        <p class="mt-1 text-sm text-gray-500">
            @if($showCurrent && $hasCurrentImage)
                Leave empty to keep current picture. 
            @endif
            Allowed: JPG, PNG, GIF, WebP. Maximum size: {{ $maxSize }}.
        </p>
    </div>
</div>

@push('scripts')
<script>
function previewImage_{{ $functionId }}(input) {
    const preview = document.getElementById('{{ $id }}-preview');
    const currentPreview = document.getElementById('current-{{ $id }}-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            // Hide current image when new one is selected
            if (currentPreview && currentPreview.closest('.space-x-4')) {
                currentPreview.closest('.space-x-4').parentElement.classList.add('hidden');
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        // If file input is cleared, show current image again
        if (preview) {
            preview.classList.add('hidden');
        }
        if (currentPreview && currentPreview.closest('.space-x-4')) {
            currentPreview.closest('.space-x-4').parentElement.classList.remove('hidden');
        }
    }
}
</script>
@endpush
