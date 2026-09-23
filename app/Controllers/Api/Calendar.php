<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CalendarModel;

class Calendar extends BaseController
{
    public function create()
    {
        $model = new CalendarModel();

        $tenantId = session('tenant_id');

        $success = $model->createCalendar(
            $tenantId,
            $this->request->getPost('name'),
            $this->request->getPost('color')
        );

        return $this->response->setJSON([
            'success' => $success
        ]);
    }
}