<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventModel;

class Event extends BaseController
{
    public function index()
    {
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        $events = (new EventModel())
            ->calendarEvents(
                service('tenant')->id(),
                $start,
                $end
            );

        $result = [];

        foreach ($events as $event) {
            $result[] = [
                'id'    => $event['id'],
                'title' => $event['title'],

                'start' => $event['start'],
                'end'   => $event['end'],

                'allDay' => (bool) $event['all_day'],

                'backgroundColor' => $event['color'],
                'borderColor'     => $event['color'],
            ];
        }

        return $this->response->setJSON($result);
    }
}