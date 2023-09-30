<?php

namespace App\Services;

class UserService extends BaseService
{
    public function get($limit = 10): array
    {
        $users = [];

        for ($i = 0; $i < $limit; $i++) {
            $response = $this->fetch(
                config('services.random_user.api_url')
            );

            if (data_get($response, 'status', 500) !== 200) {
                return $this->client->get(new ActivityService, $limit);
            }

            $users[] = $this->prepareUser(
                data_get($response['data'], 'results.0')
            );
        }

        return collect($users)->sortByDesc('full_name')->toArray();
    }

    private function prepareUser($user): array
    {
        return [
            'full_name' => data_get($user, 'name.first') .' '. data_get($user, 'name.last'),
            'phone'     => data_get($user, 'phone'),
            'email'     => data_get($user, 'email'),
            'country'   => data_get($user, 'location.country'),
        ];
    }
}
