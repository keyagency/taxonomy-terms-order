<script>
import { Head, Link } from '@statamic/cms/inertia'
import { Card, Header } from '@statamic/cms/ui'

export default {
    components: {
        Card,
        Head,
        Header,
        Link,
    },

    props: {
        title: String,
        icon: String,
        instructions: String,
        taxonomies: Array,
    },
}
</script>

<template>
    <div>
        <Head :title="title" />

        <Header :title="title" :icon="icon" />

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400" v-text="instructions" />

        <Card inset>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <Link
                    v-for="taxonomy in taxonomies"
                    :key="taxonomy.handle"
                    :href="taxonomy.url"
                    class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                    <span class="font-medium" v-text="taxonomy.title" />
                    <span class="text-sm text-gray-500 dark:text-gray-400" v-text="taxonomy.terms_count" />
                </Link>
            </div>
        </Card>
    </div>
</template>
