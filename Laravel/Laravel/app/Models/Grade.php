<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model {
    use HasFactory;
    protected $fillable = ['student_id', 'lesson_id', 'grade_value', 'comment', 'date_given'];
    protected $casts = ['date_given' => 'date'];
    public function student() { return $this->belongsTo(Student::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
}