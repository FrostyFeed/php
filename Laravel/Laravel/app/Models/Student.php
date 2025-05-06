<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    use HasFactory;
    protected $fillable = ['first_name', 'last_name', 'date_of_birth', 'class_group'];
    public function grades() { return $this->hasMany(Grade::class); }
}