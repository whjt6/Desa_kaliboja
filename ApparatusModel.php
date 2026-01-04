<?php namespace App\Models;
use CodeIgniter\Model;
class ApparatusModel extends Model
{
    protected $table = 'apparatus';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'position', 'photo', 'period', 'created_at', 'updated_at'];
}