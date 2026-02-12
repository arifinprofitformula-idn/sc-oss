<div class="p-6">
  <h2 class="text-xl font-semibold mb-4">Email Template Builder (Beta)</h2>
  <form x-data="{ subject: '{{ $template->subject ?? '' }}', body: @js($template->body ?? ''), preview: '' }" @submit.prevent>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Subject</label>
        <input x-model="subject" class="mt-1 w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium">Language</label>
        <select name="language" class="mt-1 w-full border rounded px-3 py-2">
          <option value="ID">ID</option>
          <option value="EN">EN</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Type</label>
        <input name="type" value="{{ $template->type ?? '' }}" class="mt-1 w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium">Variant (A/B)</label>
        <select name="variant" class="mt-1 w-full border rounded px-3 py-2">
          <option value="">-</option>
          <option value="A">A</option>
          <option value="B">B</option>
        </select>
      </div>
    </div>
    <div class="mt-4">
      <label class="block text-sm font-medium">Body</label>
      <textarea x-model="body" class="mt-1 w-full h-64 border rounded px-3 py-2"></textarea>
      <div class="text-xs text-gray-500 mt-2">Merge tags: ${user_name}, ${order_id}, ${reset_url}, ${cta_url}</div>
    </div>
    <div class="mt-4 flex gap-2">
      <button type="button" @click="preview = body" class="px-4 py-2 bg-blue-600 text-white rounded">Preview</button>
      <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">Save</button>
    </div>
    <div class="mt-6">
      <h3 class="font-medium mb-2">Live Preview</h3>
      <iframe class="w-full h-96 border rounded" :srcdoc="`<html><body>${preview}</body></html>`"></iframe>
    </div>
  </form>
</div>
