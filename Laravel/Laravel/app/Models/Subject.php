<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function lessons() { return $this->hasMany(Lesson::class); }
}