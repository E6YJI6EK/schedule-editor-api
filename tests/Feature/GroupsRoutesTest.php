<?php

namespace Tests\Feature;

use App\Enums\Course;
use App\Enums\EducationForm;
use App\Models\Group;
use App\Models\Institute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsRoutesTest extends TestCase
{
    use RefreshDatabase;

    private Institute $institute;

    protected function setUp(): void
    {
        parent::setUp();
        $this->institute = Institute::create(['name' => 'Tech']);
    }

    private function makeGroup(array $override = []): Group
    {
        return Group::create(array_merge([
            'name' => 'A-01',
            'course' => Course::First,
            'education_form' => EducationForm::Intramural,
            'institute_id' => $this->institute->id,
        ], $override));
    }

    // ── searchGroups ───────────────────────────────────────────────────────

    public function test_groups_search_validation_errors_when_missing_required_fields(): void
    {
        $this->getJson('/api/groups/search')->assertStatus(422);
    }

    public function test_groups_search_returns_404_when_no_results(): void
    {
        $query = http_build_query([
            'course' => Course::First->value,
            'education_form' => EducationForm::Intramural->value,
            'institute_id' => $this->institute->id,
        ]);

        $this->getJson('/api/groups/search?' . $query)
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Группы не найдены', 'status' => 404]);
    }

    public function test_groups_search_returns_results(): void
    {
        $this->makeGroup(['name' => 'ИВТ-101']);

        $query = http_build_query([
            'course' => Course::First->value,
            'education_form' => EducationForm::Intramural->value,
            'institute_id' => $this->institute->id,
        ]);

        $this->getJson('/api/groups/search?' . $query)
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Группы получены'])
            ->assertJsonCount(1, 'data');
    }

    // ── searchGroupsByName ─────────────────────────────────────────────────

    public function test_groups_search_by_name_returns_404_when_no_results(): void
    {
        $this->getJson('/api/groups/search-by-name?name=ИВТ')
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Группы не найдены', 'status' => 404]);
    }

    public function test_groups_search_by_name_returns_results(): void
    {
        $this->makeGroup(['name' => 'ИВТ-101']);
        $this->makeGroup(['name' => 'ИВТ-102']);

        $this->getJson('/api/groups/search-by-name?name=ИВТ')
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Группы получены'])
            ->assertJsonCount(2, 'data');
    }

    public function test_groups_search_by_name_min_length_validation(): void
    {
        $this->getJson('/api/groups/search-by-name?name=А')
            ->assertStatus(422);
    }

    public function test_groups_search_by_name_returns_all_when_no_name(): void
    {
        $this->makeGroup(['name' => 'ИВТ-101']);

        $this->getJson('/api/groups/search-by-name')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
