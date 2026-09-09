<?php

namespace App\Controllers;

class Calendar extends BaseController
{
    public function index()
    {
        return view('calendar/index', [
            'title' => 'Kalender',
        ]);
    }
}