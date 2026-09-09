<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';

    protected $allowedFields = [
        'name',
        'slug',
        'active'
    ];

    protected $useTimestamps = true;
}