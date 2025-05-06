<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Lesson extends Model {
    use HasFactory;
    protected $fillable = ['subject_id', 'teacher_id', 'lesson_date', 'topic', 'homework'];
    protected $casts = ['lesson_date' => 'datetime'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function grades() { return $this->hasMany(Grade::class); }
}