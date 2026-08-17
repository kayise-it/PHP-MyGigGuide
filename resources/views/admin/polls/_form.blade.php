<form action="{{ $action }}" method="POST" class="space-y-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Context</label>
        <select name="context" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            @foreach($contexts as $key => $label)
            <option value="{{ $key }}" @selected(old('context', $poll?->context) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('context')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
        <input type="text" name="question" value="{{ old('question', $poll?->question) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        @error('question')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Options (min 2)</label>
        @php
            $options = old('options', $poll?->options ?? ['', '']);
            if (count($options) < 2) { $options = array_pad($options, 2, ''); }
        @endphp
        @foreach($options as $index => $option)
        <input type="text" name="options[]" value="{{ $option }}" placeholder="Option {{ $index + 1 }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2" required>
        @endforeach
        <input type="text" name="options[]" value="" placeholder="Add another option (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2">
        @error('options')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $poll?->is_active ?? true))>
            <span class="text-sm text-gray-700">Active</span>
        </label>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Closes at (optional)</label>
        <input type="datetime-local" name="closes_at" value="{{ old('closes_at', optional($poll?->closes_at)->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Save</button>
        <a href="{{ route('admin.polls.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700">Cancel</a>
    </div>
</form>
