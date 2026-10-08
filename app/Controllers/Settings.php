<?php

namespace App\Controllers;

use App\Models\CalendarPreferenceModel;
use InvalidArgumentException;

class Settings extends BaseController
{
    public function index()
    {
        $this->response->setHeader('Cache-Control', 'private, no-store');
        $tenant = service('tenant')->id();
        if ($tenant === null) {
            return $this->response->setStatusCode(403)->setBody('Kein aktiver Mandant.');
        }
        $model = new CalendarPreferenceModel();
        $preferences = $model->preferences((int) auth()->id(), $tenant);
        $error = null;
        if ($this->request->getMethod() === 'POST') {
            try {
                $data = $this->request->getPost();
                $data['years'] = $data['years'] ?? [];
                if (!$model->store((int) auth()->id(), $tenant, $data)) {
                    throw new InvalidArgumentException('Einstellungen konnten nicht gespeichert werden.');
                }
                return redirect()->to(site_url('calendar'));
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
                $this->response->setStatusCode(422);
            }
        }
        return view('settings/index', compact('preferences', 'error') + ['title' => 'Einstellungen']);
    }
}
