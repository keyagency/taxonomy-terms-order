<?php

namespace KeyAgency\TaxonomyTermsOrder\Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use KeyAgency\TaxonomyTermsOrder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->title('Tags')->save();
        Taxonomy::make('categories')->title('Categories')->save();
    }

    #[Test]
    public function it_lists_orderable_taxonomies()
    {
        $this
            ->actingAs(tap(User::make()->email('super@example.com')->makeSuper())->save())
            ->get(cp_route('taxonomy-terms-order.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('taxonomy-terms-order::Index', false)
                ->has('taxonomies', 2)
                ->where('taxonomies.0.handle', 'tags')
                ->where('taxonomies.1.handle', 'categories'));
    }

    #[Test]
    public function it_only_lists_taxonomies_from_the_config()
    {
        config(['taxonomy-terms-order.taxonomies' => ['tags']]);

        $this
            ->actingAs(tap(User::make()->email('super@example.com')->makeSuper())->save())
            ->get(cp_route('taxonomy-terms-order.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('taxonomy-terms-order::Index', false)
                ->has('taxonomies', 1)
                ->where('taxonomies.0.handle', 'tags'));
    }

    #[Test]
    public function it_denies_users_without_any_reorder_permission()
    {
        Role::make('editor')->permissions(['access cp'])->save();

        $this
            ->actingAs(tap(User::make()->email('editor@example.com')->assignRole('editor'))->save())
            ->get(cp_route('taxonomy-terms-order.index'))
            ->assertForbidden();
    }
}
