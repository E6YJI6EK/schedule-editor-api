<?php

namespace Tests\Feature\Services;

use App\Enums\Course;
use App\Enums\EducationForm;
use App\Models\Group;
use App\Models\Institute;
use App\Services\GroupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private GroupService $service;
    private Institute $institute;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GroupService();
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

    public function test_search_returns_empty_when_none(): void
    {
        $result = $this->service->search([]);

        $this->assertCount(0, $result);
    }

    public function test_search_without_filters_returns_all(): void
    {
        $this->makeGroup(['name' => 'A-01']);
        $this->makeGroup(['name' => 'A-02']);

        $result = $this->service->search([]);

        $this->assertCount(2, $result);
    }

    public function test_search_by_institute_id(): void
    {
        $other = Institute::create(['name' => 'Other']);
        $this->makeGroup(['institute_id' => $this->institute->id]);
        $this->makeGroup(['name' => 'B-01', 'institute_id' => $other->id]);

        $result = $this->service->search(['institute_id' => $this->institute->id]);

        $this->assertCount(1, $result);
        $this->assertEquals($this->institute->id, $result->first()->institute_id);
    }

    public function test_search_by_course(): void
    {
        $this->makeGroup(['name' => 'A-01', 'course' => Course::First]);
        $this->makeGroup(['name' => 'A-02', 'course' => Course::Second]);
        $this->makeGroup(['name' => 'A-03', 'course' => Course::First]);

        $result = $this->service->search(['course' => Course::First]);

        $this->assertCount(2, $result);
    }

    public function test_search_by_education_form(): void
    {
        $this->makeGroup(['name' => 'A-01', 'education_form' => EducationForm::Intramural]);
        $this->makeGroup(['name' => 'A-02', 'education_form' => EducationForm::Extramural]);

        $result = $this->service->search(['education_form' => EducationForm::Extramural]);

        $this->assertCount(1, $result);
        $this->assertEquals(EducationForm::Extramural, $result->first()->education_form);
    }

    public function test_search_by_name_partial_match(): void
    {
        $this->makeGroup(['name' => 'ИВТ-101']);
        $this->makeGroup(['name' => 'ИВТ-102']);
        $this->makeGroup(['name' => 'ПИ-201']);

        $result = $this->service->search(['name' => 'ИВТ']);

        $this->assertCount(2, $result);
    }

    public function test_search_combines_multiple_filters(): void
    {
        $other = Institute::create(['name' => 'Other']);
        $this->makeGroup(['name' => 'A-01', 'course' => Course::First, 'institute_id' => $this->institute->id]);
        $this->makeGroup(['name' => 'A-02', 'course' => Course::Second, 'institute_id' => $this->institute->id]);
        $this->makeGroup(['name' => 'B-01', 'course' => Course::First, 'institute_id' => $other->id]);

        $result = $this->service->search([
            'institute_id' => $this->institute->id,
            'course' => Course::First,
        ]);

        $this->assertCount(1, $result);
        $this->assertEquals('A-01', $result->first()->name);
    }

    public function test_search_limits_to_50(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->makeGroup(['name' => "Group-$i"]);
        }

        $result = $this->service->search([]);

        $this->assertCount(50, $result);
    }
}
