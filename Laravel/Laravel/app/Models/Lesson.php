<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Lesson extends Model {
    use HasFactory;
    protected $fillable = ['subject_id', 'teacher_id', 'lesson_date', 'topic', 'homework'];
 protected $casts = [
        'lesson_date' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s', 
        'updated_at' => 'datetime:Y-m-d H:i:s', 
    ];    
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function grades() { return $this->hasMany(Grade::class); }
      public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }
        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }
        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }
        if (!empty($filters['lesson_date'])) {
            $query->where('lesson_date', Carbon::parse($filters['lesson_date']));
        }
        if (!empty($filters['lesson_date_from'])) {
            $query->where('lesson_date', '>=', Carbon::parse($filters['lesson_date_from']));
        }
        if (!empty($filters['lesson_date_to'])) {
            $query->where('lesson_date', '<=', Carbon::parse($filters['lesson_date_to']));
        }
        if (!empty($filters['topic'])) {
            $query->where('topic', 'like', '%' . $filters['topic'] . '%');
        }
        if (array_key_exists('homework', $filters)) {
            if ($filters['homework'] === null || strtolower((string)$filters['homework']) === 'null') {
                $query->whereNull('homework');
            } else {
                $query->where('homework', 'like', '%' . $filters['homework'] . '%');
            }
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