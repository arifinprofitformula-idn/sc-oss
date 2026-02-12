<div x-data="emailTemplateEditor()" x-init="init()" class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mt-6 border border-gray-200 dark:border-gray-700">
    <!-- Header & Selector -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 border-b pb-4 dark:border-gray-700">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Visual Template Editor (WYSIWYG)</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Customize your email templates with a live preview editor.</p>
        </div>
        <div class="w-full sm:w-64">
            <select x-model="selectedTemplateId" @change="loadTemplate(selectedTemplateId)" 
                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">-- Select Template --</option>
                <template x-for="t in templates" :key="t.id">
                    <option :value="t.id" x-text="t.name"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-12">
        <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="ml-2 text-gray-600 dark:text-gray-400">Loading editor resources...</span>
    </div>

    <!-- Editor Area -->
    <div x-show="currentTemplate && !loading" style="display: none;" class="space-y-6">
        
        <!-- Toolbar Actions (Top) -->
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">
                <span x-text="statusMessage" :class="statusType === 'success' ? 'text-green-600' : 'text-gray-500'"></span>
            </div>
            <div class="flex space-x-2">
                <button @click="openPreview()" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                </button>
                <button @click="saveTemplate()" type="button" :disabled="saving" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>

        <!-- Subject Line -->
        <!-- Draft Banner -->
        <div x-show="hasDraft" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 dark:bg-yellow-900/20 dark:border-yellow-600" x-transition style="display: none;">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 dark:text-yellow-200">
                        Unsaved draft found from <span x-text="draftTimestamp" class="font-medium"></span>.
                        <button @click="restoreDraft()" class="font-medium underline hover:text-yellow-600 dark:hover:text-yellow-100 mx-1">Restore</button>
                        or
                        <button @click="discardDraft()" class="font-medium underline hover:text-yellow-600 dark:hover:text-yellow-100 mx-1">Discard</button>
                    </p>
                </div>
            </div>
        </div>

        <div>
            <label for="template_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject Line</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input type="text" x-model="form.subject" id="template_subject" 
                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300" 
                       placeholder="Enter email subject...">
            </div>
            <p class="mt-1 text-xs text-gray-500">Supports merge tags like @{{ customer_name }}</p>
        </div>

        <!-- TinyMCE Editor Container -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Content</label>
            <textarea id="tinymce-editor" class="w-full h-96"></textarea>
        </div>

        <!-- Merge Tags Helper -->
        <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-md border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                <svg class="h-4 w-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Available Merge Tags
            </h4>
            <div class="flex flex-wrap gap-2">
                <template x-for="tag in (currentTemplate?.variables || [])" :key="tag">
                    <button @click="insertTag(tag)" 
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 cursor-pointer dark:bg-indigo-900 dark:text-indigo-200"
                            title="Click to insert">
                        @{{ tag }}
                        <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </template>
                <span x-show="!currentTemplate?.variables?.length" class="text-xs text-gray-500 italic">No variables defined for this template.</span>
            </div>
        </div>

        <!-- Version History -->
        <div class="border-t pt-6 dark:border-gray-700" x-data="{ showHistory: false }">
            <button @click="showHistory = !showHistory" class="flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="showHistory ? 'Hide Version History' : 'Show Version History'"></span>
            </button>
            
            <div x-show="showHistory" class="mt-4 space-y-3" x-transition>
                <template x-for="history in (currentTemplate?.histories || [])" :key="history.id">
                    <div class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded dark:bg-gray-800">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-gray-100" x-text="new Date(history.created_at).toLocaleString()"></span>
                            <span class="text-gray-500 ml-2">by <span x-text="history.user?.name || 'System'"></span></span>
                        </div>
                        <button @click="revertTemplate(history.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-medium">
                            Revert to this version
                        </button>
                    </div>
                </template>
                <div x-show="!currentTemplate?.histories?.length" class="text-sm text-gray-500 italic">No history available.</div>
            </div>
        </div>

    </div>

    <!-- Empty State -->
    <div x-show="!currentTemplate && !loading" class="text-center py-12 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <p class="mt-2 text-sm font-medium">Select a template above to start editing</p>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('emailTemplateEditor', () => ({
            templates: [],
            selectedTemplateId: '',
            currentTemplate: null,
            loading: false,
            saving: false,
            statusMessage: '',
            statusType: '',
            hasDraft: false,
            draftTimestamp: null,
            form: {
                subject: '',
                body: ''
            },
            editor: null,

            init() {
                this.fetchTemplates();
                this.$watch('form.subject', () => this.saveDraft());
            },

            async fetchTemplates() {
                try {
                    const response = await fetch('/admin/integrations/email/templates');
                    this.templates = await response.json();
                } catch (error) {
                    console.error('Error fetching templates:', error);
                }
            },

            async loadTemplate(id) {
                if (!id) {
                    this.currentTemplate = null;
                    if (this.editor) this.editor.setContent('');
                    this.hasDraft = false;
                    return;
                }

                this.loading = true;
                this.statusMessage = '';
                
                try {
                    const response = await fetch(`/admin/integrations/email/templates/${id}`);
                    this.currentTemplate = await response.json();
                    this.form.subject = this.currentTemplate.subject;
                    this.form.body = this.currentTemplate.body;

                    this.checkDraft();
                    this.initTinyMCE();
                } catch (error) {
                    console.error('Error loading template:', error);
                    alert('Failed to load template');
                } finally {
                    this.loading = false;
                }
            },

            checkDraft() {
                if (!this.currentTemplate) return;
                const key = `email_template_draft_${this.currentTemplate.id}`;
                const draft = localStorage.getItem(key);
                
                this.hasDraft = false;
                if (draft) {
                    try {
                        const parsed = JSON.parse(draft);
                        if (parsed.body !== this.currentTemplate.body || parsed.subject !== this.currentTemplate.subject) {
                            this.hasDraft = true;
                            this.draftTimestamp = parsed.timestamp;
                        } else {
                            localStorage.removeItem(key);
                        }
                    } catch (e) {
                        localStorage.removeItem(key);
                    }
                }
            },

            saveDraft() {
                if (!this.currentTemplate || this.loading) return;
                const key = `email_template_draft_${this.currentTemplate.id}`;
                const content = this.editor ? this.editor.getContent() : this.form.body;
                
                if (content === this.currentTemplate.body && this.form.subject === this.currentTemplate.subject) {
                     return; 
                }

                const data = {
                    subject: this.form.subject,
                    body: content,
                    timestamp: new Date().toLocaleString()
                };
                localStorage.setItem(key, JSON.stringify(data));
            },
            
            restoreDraft() {
                 const key = `email_template_draft_${this.currentTemplate.id}`;
                 const draft = localStorage.getItem(key);
                 if (draft) {
                     const parsed = JSON.parse(draft);
                     this.form.subject = parsed.subject;
                     this.form.body = parsed.body;
                     if (this.editor) {
                         this.editor.setContent(parsed.body);
                     }
                     this.statusMessage = 'Draft restored from ' + parsed.timestamp;
                     this.hasDraft = false;
                 }
            },

            discardDraft() {
                 if (this.currentTemplate) {
                    const key = `email_template_draft_${this.currentTemplate.id}`;
                    localStorage.removeItem(key);
                 }
                 this.hasDraft = false;
            },

            initTinyMCE() {
                if (tinymce.get('tinymce-editor')) {
                    tinymce.get('tinymce-editor').remove();
                }

                this.$nextTick(() => {
                    tinymce.init({
                        selector: '#tinymce-editor',
                        height: 500,
                        menubar: true,
                        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code preview fullscreen',
                        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | code preview fullscreen',
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                        setup: (editor) => {
                            this.editor = editor;
                            editor.on('init', () => {
                                if (this.currentTemplate) {
                                    editor.setContent(this.currentTemplate.body);
                                }
                            });
                            editor.on('change', () => {
                                this.form.body = editor.getContent();
                                this.saveDraft();
                            });
                            editor.on('keyup', () => {
                                this.saveDraft();
                            });
                        }
                    });
                });
            },

            insertTag(tag) {
                if (this.editor) {
                    this.editor.insertContent('@{{' + tag + '}}');
                } else {
                    alert('Editor not initialized');
                }
            },

            async saveTemplate() {
                if (!this.currentTemplate) return;
                
                this.form.body = this.editor.getContent(); // Ensure latest content
                this.saving = true;
                this.statusMessage = 'Saving...';

                try {
                    const response = await fetch(`/admin/integrations/email/templates/${this.currentTemplate.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        this.statusMessage = 'Saved successfully!';
                        this.statusType = 'success';
                        
                        this.discardDraft();
                        
                        // Refresh history
                        this.loadTemplate(this.currentTemplate.id);
                        
                        setTimeout(() => { this.statusMessage = ''; }, 3000);
                    } else {
                        throw new Error(result.message || 'Save failed');
                    }
                } catch (error) {
                    console.error('Error saving:', error);
                    this.statusMessage = 'Error saving template.';
                    this.statusType = 'error';
                } finally {
                    this.saving = false;
                }
            },

            async revertTemplate(historyId) {
                if (!confirm('Are you sure you want to revert to this version? Current changes will be saved to history.')) return;

                this.loading = true;
                try {
                    const response = await fetch(`/admin/integrations/email/templates/revert/${historyId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert('Reverted successfully');
                        this.loadTemplate(this.currentTemplate.id);
                    }
                } catch (error) {
                    console.error('Error reverting:', error);
                    alert('Failed to revert');
                } finally {
                    this.loading = false;
                }
            },
            
            openPreview() {
                if (!this.editor) return;
                const content = this.editor.getContent();
                // Basic window preview
                const w = window.open('', '_blank');
                w.document.write(content);
                w.document.close();
            }
        }))
    });
</script>
@endpush
