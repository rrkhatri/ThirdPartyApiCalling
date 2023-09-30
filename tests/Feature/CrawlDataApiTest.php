<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CrawlDataApiTest extends TestCase
{
    private string $crawlerApi = '/api/crawl-data';
    protected string $randomUserApi;
    protected string $boredActivitiesApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->randomUserApi = config('services.random_user.api_url');
        $this->boredActivitiesApi = config('services.bored_activity.api_url');
    }

    public function test_that_crawl_api_returns_success(): void
    {
        $response = $this->get($this->crawlerApi);

        $response->assertStatus(200);
    }

    public function test_throws_validation_error_on_passing_less_than_lowest_limit(): void
    {
        $response = $this->get($this->crawlerApi . '?limit=0');

        $response->assertStatus(422)->assertHeader('Content-Type', 'application/xml');
    }

    public function test_throws_validation_error_on_passing_more_than_max_limit(): void
    {
        $response = $this->withHeaders(['Content-Type', 'application/xml'])->get($this->crawlerApi . '?limit=50');

        $response->assertStatus(422)->assertHeader('Content-Type', 'application/xml');
    }

    public function test_count_of_result_equals_to_passed_limit(): void
    {
        $response = $this->get($this->crawlerApi . '?limit=2');

        $results = data_get($this->xmlToArray($response->getContent()), 'data', []);

        $this->assertCount(2, $results);
    }

    public function test_that_response_is_valid_xml(): void
    {
        $response = $this->withHeaders(['Content-Type', 'application/xml'])->get($this->crawlerApi . '?limit=2');

        $response->assertStatus(200)->assertHeader('Content-Type', 'application/xml');
    }

    public function test_that_response_contains_users_fields()
    {
        $response = $this->get($this->crawlerApi . '?limit=1');
        $response->assertStatus(200);
        $xml = simplexml_load_string($response->getContent());
        $user = $xml->data;

        $this->assertNotNull($user->full_name);
        $this->assertNotNull($user->phone);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->country);
    }

    public function test_that_response_contains_activities_if_users_api_down()
    {
        Http::fake([
            $this->randomUserApi => Http::response([], 500)
        ]);

        $activity = [
            'activity' => 'test',
            'type'     => '1234567890',
            'key'      => 'test@test.com'
        ];

        Http::fake([
            $this->boredActivitiesApi => Http::response($activity, 200)
        ]);

        $response = $this->get($this->crawlerApi . '?limit=1');
        $response->assertStatus(200);
        $response = $this->xmlToArray($response->getContent());

        $this->assertEquals($response['data'], $activity);
    }

    public function test_response_contains_sorted_users_data_by_full_name()
    {
        Http::fake([
            $this->randomUserApi => Http::sequence()
                ->push($this->getMockUserData())
                ->push($this->getMockUserData(1)),
        ]);

        $response = $this->get($this->crawlerApi . '?limit=2');

        $response->assertStatus(200);

        $this->assertEquals(
            $this->getExpectedSortedUserData(),
            $this->xmlToArray($response->getContent())
        );
    }

    private function getMockUserData($index = 0)
    {
        $user = data_get([
            [
                "name"     => [
                    "title" => "Mr",
                    "first" => "John",
                    "last"  => "Doe"
                ],
                'phone'    => '917-134-303',
                'email'    => 'sedef.akay@example.com',
                'location' => [
                    'country' => 'Turkey'
                ]
            ],
            [
                "name"     => [
                    "title" => "Mr",
                    "first" => "Eden",
                    "last"  => "Roger"
                ],
                'phone'    => '817-134-303',
                'email'    => 'eden.roger@example.com',
                'location' => [
                    'country' => 'France'
                ]
            ],
        ], $index);

        return [
            'results' => [
                $user
            ]
        ];
    }

    private function getExpectedSortedUserData()
    {
        return [
            'data' => [
                [
                    'full_name' => 'John Doe',
                    'phone'     => '917-134-303',
                    'email'     => 'sedef.akay@example.com',
                    'country'   => 'Turkey',
                ],
                [
                    'full_name' => 'Eden Roger',
                    'phone'     => '817-134-303',
                    'email'     => 'eden.roger@example.com',
                    'country'   => 'France'
                ],
            ]
        ];
    }

    public function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml)), 1);
    }
}
