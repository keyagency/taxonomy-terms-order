import Index from './pages/Index.vue'
import OrderTerms from './pages/OrderTerms.vue'

Statamic.booting(() => {
    Statamic.$inertia.register('taxonomy-terms-order::Index', Index)
    Statamic.$inertia.register('taxonomy-terms-order::OrderTerms', OrderTerms)
})
