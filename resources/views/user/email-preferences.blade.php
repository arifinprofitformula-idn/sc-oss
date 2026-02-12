<div class="p-6">
  <h2 class="text-xl font-semibold mb-4">Email Preferences</h2>
  <div x-data="{
      prefs: [],
      load() {
        fetch('/api/user/email-preferences', { headers: { 'Accept': 'application/json' }})
          .then(r => r.json()).then(d => this.prefs = d);
      },
      toggle(type, enabled) {
        fetch('/api/user/email-preferences', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ type, enabled })
        }).then(() => this.load());
      }
    }" x-init="load()">
    <template x-for="p in prefs" :key="p.id">
      <div class="flex items-center justify-between border rounded px-3 py-2 mb-2">
        <div>
          <div class="font-medium" x-text="p.type"></div>
        </div>
        <button @click="toggle(p.type, !p.enabled)" 
                class="px-3 py-1 rounded" 
                :class="p.enabled ? 'bg-emerald-600 text-white' : 'bg-gray-300'">
          <span x-text="p.enabled ? 'Enabled' : 'Disabled'"></span>
        </button>
      </div>
    </template>
  </div>
</div>
