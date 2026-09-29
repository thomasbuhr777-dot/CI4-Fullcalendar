<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CalendarModel;
use CodeIgniter\HTTP\ResponseInterface;

class Calendar extends BaseController
{
    public function create(): ResponseInterface
    {
        $tenantId = service('tenant')->id();
        if ($tenantId === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Kein aktiver Mandant.',
            ]);
        }

        $name = trim((string) $this->request->getPost('name'));
        $color = strtoupper(trim((string) $this->request->getPost('color')));

        if ($name === '' || mb_strlen($name) > 100 || preg_match('/^#[0-9A-F]{6}$/', $color) !== 1) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Name und eine gültige Farbe sind erforderlich.',
            ]);
        }

        $model = new CalendarModel();
        $id = $model->createCalendar($tenantId, $name, $color);
        if ($id === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Kalender konnte nicht angelegt werden.',
            ]);
        }

        return $this->response->setStatusCode(201)->setJSON([
            'success' => true,
            'calendar' => $model->findTenantCalendar($tenantId, $id),
        ]);
    }
}
