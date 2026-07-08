<?php

namespace KeyAgency\TaxonomyTermsOrder\Tests\Feature;

use KeyAgency\TaxonomyTermsOrder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;

class ReorderTermsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->title('Tags')->save();

        foreach (['alpha', 'bravo', 'charlie'] as $slug) {
            Term::make()->taxonomy('tags')->slug($slug)->data(['title' => ucfirst($slug)])->save();
        }
    }

    protected function superUser()
    {
        return tap(User::make()->email('super@example.com')->makeSuper())->save();
    }

    #[Test]
    public function it_reorders_terms_and_makes_order_the_default_sort()
    {
        $response = $this
            ->actingAs($this->superUser())
            ->post(cp_route('taxonomy-terms-order.reorder', 'tags'), [
                'ids' => ['tags::charlie', 'tags::alpha', 'tags::bravo'],
            ]);

        $response->assertOk();

        $this->assertSame(1, Term::find('tags::charlie')->get('order'));
        $this->assertSame(2, Term::find('tags::alpha')->get('order'));
        $this->assertSame(3, Term::find('tags::bravo')->get('order'));

        $taxonomy = Taxonomy::findByHandle('tags');
        $this->assertSame('order', $taxonomy->sortField());
        $this->assertSame('asc', $taxonomy->sortDirection());
    }

    #[Test]
    public function it_appends_terms_missing_from_the_payload()
    {
        $this
            ->actingAs($this->superUser())
            ->post(cp_route('taxonomy-terms-order.reorder', 'tags'), [
                'ids' => ['tags::charlie'],
            ])
            ->assertOk();

        $this->assertSame(1, Term::find('tags::charlie')->get('order'));
        $this->assertSame(2, Term::find('tags::alpha')->get('order'));
        $this->assertSame(3, Term::find('tags::bravo')->get('order'));
    }

    #[Test]
    public function it_ignores_ids_from_other_taxonomies()
    {
        Taxonomy::make('other')->title('Other')->save();
        Term::make()->taxonomy('other')->slug('delta')->data(['title' => 'Delta'])->save();

        $this
            ->actingAs($this->superUser())
            ->post(cp_route('taxonomy-terms-order.reorder', 'tags'), [
                'ids' => ['other::delta', 'tags::bravo', 'tags::alpha', 'tags::charlie'],
            ])
            ->assertOk();

        $this->assertNull(Term::find('other::delta')->get('order'));
        $this->assertSame(1, Term::find('tags::bravo')->get('order'));
    }

    #[Test]
    public function it_denies_users_without_the_reorder_permission()
    {
        Role::make('editor')->permissions(['access cp'])->save();
        $user = tap(User::make()->email('editor@example.com')->assignRole('editor'))->save();

        $this
            ->actingAs($user)
            ->post(cp_route('taxonomy-terms-order.reorder', 'tags'), [
                'ids' => ['tags::charlie', 'tags::alpha', 'tags::bravo'],
            ])
            ->assertForbidden();

        $this->assertNull(Term::find('tags::charlie')->get('order'));
    }

    #[Test]
    public function it_returns_404_for_taxonomies_that_are_not_orderable()
    {
        config(['taxonomy-terms-order.taxonomies' => ['categories']]);

        $this
            ->actingAs($this->superUser())
            ->post(cp_route('taxonomy-terms-order.reorder', 'tags'), [
                'ids' => ['tags::charlie', 'tags::alpha', 'tags::bravo'],
            ])
            ->assertNotFound();
    }

    #[Test]
    public function it_renders_the_order_page()
    {
        $this
            ->actingAs($this->superUser())
            ->get(cp_route('taxonomy-terms-order.show', 'tags'))
            ->assertOk()
            ->assertSee('taxonomy-terms-order::OrderTerms');
    }
}
