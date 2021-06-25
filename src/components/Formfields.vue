<template>
    <card title="Formfields">
        <div class="w-full inline-flex space-x-1 mb-4">
            <button class="button accent" v-for="f in formfields" :key="`button-${f.formfield.type}`" v-scrollTo="`formfield-${f.formfield.type}`">
                {{ f.formfield.name }}
            </button>
        </div>
        <template #actions>
            <div class="inline-flex space-x-1 items-center">
                <button class="button accent" @click="save" :disabled="isSaving">Save</button>
                <button class="button accent" @click="clearPreferences" :disabled="isSaving">Clear</button>
                <select class="input small" @input="setAction($event.target.value)">
                    <option value="browse" :selected="action == 'browse'">Browse</option>
                    <option value="read" :selected="action == 'read'">Read</option>
                    <option value="edit" :selected="action == 'edit'">Edit</option>
                    <option value="add" :selected="action == 'add'">Add</option>
                </select>
            </div>
        </template>
        <collapsible v-for="f in formfields" :key="f.formfield.type" :title="f.formfield.name" :titleSize="5" :id="`formfield-${f.formfield.type}`">
            <template #actions>
                <div class="inline-flex space-x-1">
                    <slide-in :title="__('voyager::generic.options')">
                        <template #actions>
                            <locale-picker />
                        </template>
                        <component
                            :is="f.formfield.builder_component"
                            v-model:options="f.list_options"
                            :column="{}"
                            :columns="[]"
                            action="list-options" />
                        <div class="input-group mt-2">
                            <label class="label">{{ __('voyager::generic.classes') }}</label>
                            <input type="text" class="input w-full" v-model="f.list_options.classes">
                        </div>
                        <template #opener>
                            <button class="button accent">List options</button>
                        </template>
                    </slide-in>
                    <slide-in :title="__('voyager::generic.options')">
                        <template #actions>
                            <locale-picker />
                        </template>
                        <component
                            :is="f.formfield.builder_component"
                            v-model:options="f.view_options"
                            :column="{}"
                            :columns="[]"
                            action="view-options" />
                        <div class="input-group mt-2">
                            <label class="label">{{ __('voyager::generic.classes') }}</label>
                            <input type="text" class="input w-full" v-model="f.view_options.classes">
                        </div>
                        <template #opener>
                            <button class="button accent">View options</button>
                        </template>
                    </slide-in>
                </div>
            </template>
            <card no-header>
                <component
                    v-if="action == 'browse'"
                    :is="f.formfield.component"
                    :options="f.list_options"
                    :column="{}"
                    :action="action"
                    v-model="f.value"
                />
                <component
                    v-else
                    :is="f.formfield.component"
                    :options="f.view_options"
                    :column="{}"
                    :action="action"
                    v-model="f.value"
                />
            </card>
            
            <textarea class="input w-full mt-2" :value="JSON.stringify(f.value, null, 2)"></textarea>
        </collapsible>
    </card>
</template>

<script>
export default {
    props: ['formfields'],
    directives: { scrollTo: window.scrollTo },
    data() {
        return {
            action: 'add',
            isSaving: false,
            output: this.formfields,
        };
    },
    methods: {
        save(e = null) {
            if (this.isSaving) {
                return;
            }
            if (typeof e === 'object' && e instanceof KeyboardEvent) {
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                } else {
                    return;
                }
            }
            if (this.action == 'browse' || this.action == 'read') {
                new this.$notification('Can not save when browsing or reading data').color('yellow').timeout().show();

                return;
            }
            this.isSaving = true;
            axios({
                method: 'put',
                url: this.route('voyager.voyager-formfields'),
                data: {
                    data: this.output,
                    action: this.action,
                }
            })
            .then((response) => {
                new this
                .$notification('Saved values and options!')
                .color('green').timeout().show();
            })
            .catch((response) => {})
            .then(() => {
                this.isSaving = false;
            });
        },
        setAction(action) {
            axios({
                method: 'post',
                url: this.route('voyager.voyager-formfields'),
                data: {
                    action: action,
                }
            })
            .then((response) => {
                this.action = action;
            })
            .catch((response) => {})
            .then(() => {
                this.isSaving = false;
            });
        },
        clearPreferences() {
             axios({
                method: 'post',
                url: this.route('voyager.voyager-formfields-clear'),
            })
            .then((response) => {
                new this
                .$notification('Everything cleared')
                .color('green').timeout().show();

                document.location.reload(true);
            })
            .catch((response) => {})
            .then(() => {
                this.isSaving = false;
            });
        }
    },
    created() {
        this.$watch(() => this.formfields, () => {
            this.output = this.formfields;
        }, { deep: true, immediate: true });
    },
    mounted() {
        document.addEventListener('keydown', this.save);
    },
    unmounted() {
        document.removeEventListener('keydown', this.save);
    },
};
</script>