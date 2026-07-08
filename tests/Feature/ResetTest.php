<?php

namespace KeyAgency\TaxonomyTermsOrder\Tests\Feature;

use KeyAgency\TaxonomyTermsOrder\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;

class ResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Taxonomy::make('tags')->title('Tags')->setSortField('order')->setSortDirection('asc')->save();

        Term::make()->taxonomy('tags')->slug('alpha')->data(['title' => 'Alpha', 'order' => 2])->save();
        Term::make()->taxonomy('tags')->slug('bravo')->data(['title' => 'Bravo', 'order' => 1])->save();
    }

    #[Test]
    public function it_removes_the_order_from_all_terms_and_restores_the_default_sort()
    {
        $this
            ->actingAs(tap(User::make()->email('super@example.com')->makeSuper())->save())
            ->post(cp_route('taxonomy-terms-order.reset', 'tags'))
            ->assertOk();

        $this->assertNull(Term::find('tags::alpha')->get('order'));
        $this->assertNull(Term::find('tags::bravo')->get('order'));

        $taxonomy = Taxonomy::findByHandle('tags');
        $this->assertSame('title', $taxonomy->sortField());
        $this->assertSame('asc', $taxonomy->sortDirection());
    }

    #[Test]
    public function it_denies_users_without_the_reorder_permission()
    {
        Role::make('editor')->permissions(['access cp'])->save();

        $this
            ->actingAs(tap(User::make()->email('editor@example.com')->assignRole('editor'))->save())
            ->post(cp_route('taxonomy-terms-order.reset', 'tags'))
            ->assertForbidden();

        $this->assertSame(2, Term::find('tags::alpha')->get('order'));
    }
}
