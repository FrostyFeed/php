<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    use HasFactory;
    protected $fillable = ['first_name', 'last_name', 'email', 'specialization'];
    public function lessons() { return $this->hasMany(Lesson::class); }
}