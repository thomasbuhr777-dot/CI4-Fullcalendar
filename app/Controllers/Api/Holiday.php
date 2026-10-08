<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CalendarPreferenceModel;
use Throwable;

class Holiday extends BaseController
{
    public function index()
    {
        $this->response->setHeader('Cache-Control', 'private, no-store');
        $tenant = service('tenant')->id();
        if ($tenant === null) return $this->response->setStatusCode(403)->setJSON(['message' => 'Kein aktiver Mandant.']);
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');
        if (!is_string($start) || !is_string($end) || !preg_match('/^\d{4}-\d{2}-\d{2}(T.*)?$/D', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}(T.*)?$/D', $end)) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Ungültiger Kalenderzeitraum.']);
        }
        try {
            return $this->response->setJSON(service('holidays')->feed(
                (new CalendarPreferenceModel())->preferences((int) auth()->id(), $tenant), $start, $end
            ));
        } catch (Throwable) {
            return $this->response->setStatusCode(422)->setJSON(['message' => 'Ungültiger Kalenderzeitraum.']);
        }
    }
}
