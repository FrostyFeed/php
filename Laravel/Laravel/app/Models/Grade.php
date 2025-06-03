<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model {
    use HasFactory;
    protected $fillable = ['student_id', 'lesson_id', 'grade_value', 'comment', 'date_given'];
  protected $casts = [
        'date_given' => 'date',
        'created_at' => 'datetime:Y-m-d H:i:s', 
        'updated_at' => 'datetime:Y-m-d H:i:s', 
    ];    
    public function student() { return $this->belongsTo(Student::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
     public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['lesson_id'])) {
            $query->where('lesson_id', $filters['lesson_id']);
        }
        if (!empty($filters['grade_value'])) {
            $query->where('grade_value', 'like', '%' . $filters['grade_value'] . '%');
        }
        if (array_key_exists('comment', $filters)) { 
            if ($filters['comment'] === null || strtolower((string)$filters['comment']) === 'null') {
                $query->whereNull('comment');
            } else {
                $query->where('comment', 'like', '%' . $filters['comment'] . '%');
            }
        }
        if (!empty($filters['date_given'])) {
            $query->whereDate('date_given', Carbon::parse($filters['date_given'])->toDateString());
        }
        if (!empty($filters['date_given_from'])) {
            $query->whereDate('date_given', '>=', Carbon::parse($filters['date_given_from'])->toDateString());
        }
        if (!empty($filters['date_given_to'])) {
            $query->whereDate('date_given', '<=', Carbon::parse($filters['date_given_to'])->toDateString());
        }
        if (!empty($filters['created_at_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_at_from']));
        }
        if (!empty($filters['created_at_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_at_to']));
        }
        if (!empty($filters['updated_at_from'])) {
            $query->where('updated_at', '>=', Carbon::parse($filters['updated_at_from']));
        }
        if (!empty($filters['updated_at_to'])) {
            $query->where('updated_at', '<=', Carbon::parse($filters['updated_at_to']));
        }
        return $query;
    }
}