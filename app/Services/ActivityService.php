<?php

namespace App\Services;

class ActivityService extends BaseService
{
    public function get($limit = 10): array
    {
        $activities = [];

        for ($i = 0; $i < $limit; $i++) {
            $response = $this->fetch(
                config('services.bored_activity.api_url')
            );

            if (data_get($response, 'status', 500) !== 200) {
                return $this->client->get(new UserService, $limit);
            }

            $activities[] = $this->prepareActivity(
                data_get($response, 'data')
            );
        }

        return collect($activities)->sortBy('type')->toArray();
    }

    public function prepareActivity($activity): array
    {
        return [
            'activity' => data_get($activity, 'activity'),
            'key'      => data_get($activity, 'key'),
            'type'     => data_get($activity, 'type')
        ];
    }
}
