<script>
import { SortableList } from '@statamic/cms'
import { Head } from '@statamic/cms/inertia'
import { Button, Card, ConfirmationModal, DragHandle, Header } from '@statamic/cms/ui'

export default {
    components: {
        Button,
        Card,
        ConfirmationModal,
        DragHandle,
        Head,
        Header,
        SortableList,
    },

    props: {
        taxonomy: String,
        taxonomyTitle: String,
        title: String,
        icon: String,
        instructions: String,
        goToTermsLabel: String,
        resetLabel: String,
        resetConfirmation: String,
        terms: Array,
        submitUrl: String,
        resetUrl: String,
        listingUrl: String,
    },

    data() {
        return {
            items: this.terms.map(term => ({ ...term })),
            initialIds: this.terms.map(term => term.id),
            saving: false,
            resetting: false,
            showResetConfirmation: false,
            saveKeyBinding: null,
        }
    },

    computed: {
        isDirty() {
            return this.items.map(item => item.id).join(',') !== this.initialIds.join(',')
        },

        hasOrder() {
            return this.items.some(item => item.order)
        },
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+s'], event => {
            event.preventDefault()
            this.save()
        })
    },

    mounted() {
        window.addEventListener('beforeunload', this.warnBeforeUnload)
    },

    beforeUnmount() {
        this.saveKeyBinding.destroy()
        window.removeEventListener('beforeunload', this.warnBeforeUnload)
    },

    methods: {
        save() {
            if (!this.isDirty || this.saving) return

            this.saving = true

            this.$axios
                .post(this.submitUrl, { ids: this.items.map(item => item.id) })
                .then(response => {
                    this.items = this.items.map((item, index) => ({ ...item, order: index + 1 }))
                    this.initialIds = this.items.map(item => item.id)
                    this.$toast.success(response.data.message)
                })
                .catch(() => this.$toast.error(__('Something went wrong')))
                .finally(() => (this.saving = false))
        },

        reset() {
            if (this.resetting) return

            this.resetting = true

            this.$axios
                .post(this.resetUrl)
                .then(response => {
                    this.items = this.items
                        .map(item => ({ ...item, order: null }))
                        .sort((a, b) => a.title.localeCompare(b.title))
                    this.initialIds = this.items.map(item => item.id)
                    this.$toast.success(response.data.message)
                })
                .catch(() => this.$toast.error(__('Something went wrong')))
                .finally(() => {
                    this.resetting = false
                    this.showResetConfirmation = false
                })
        },

        warnBeforeUnload(event) {
            if (this.isDirty) event.preventDefault()
        },
    },
}
</script>

<template>
    <div>
        <Head :title="[taxonomyTitle, title]" />

        <Header :title="`${taxonomyTitle} — ${title}`" :icon="icon">
            <Button
                v-if="hasOrder"
                :text="resetLabel"
                @click="showResetConfirmation = true"
            />
            <Button :href="listingUrl" :text="goToTermsLabel" />
            <Button
                variant="primary"
                :text="__('Save Order')"
                :disabled="!isDirty"
                :loading="saving"
                @click="save"
            />
        </Header>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400" v-text="instructions" />

        <Card inset>
            <SortableList
                v-model="items"
                :distance="5"
                :mirror="false"
                vertical
                item-class="term-row"
                handle-class="term-row"
            >
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="term-row flex cursor-grab items-center gap-3 px-4 py-2.5 [&.draggable-source--is-dragging]:opacity-50"
                    >
                        <DragHandle />
                        <div class="min-w-0 flex-1">
                            <a :href="item.edit_url" class="font-medium hover:underline" v-text="item.title" @click.stop />
                            <div class="font-mono text-2xs text-gray-500 dark:text-gray-400" v-text="item.slug" />
                        </div>
                    </div>
                </div>
            </SortableList>
        </Card>

        <ConfirmationModal
            :open="showResetConfirmation"
            :title="resetLabel"
            :body-text="resetConfirmation"
            :button-text="resetLabel"
            :busy="resetting"
            danger
            @confirm="reset"
            @update:open="showResetConfirmation = $event"
        />
    </div>
</template>
