<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
    use HasFactory;
    protected $fillable = ['first_name', 'last_name', 'email', 'specialization'];
     protected $casts = [ 
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    public function lessons() { return $this->hasMany(Lesson::class); }
     public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }
        if (!empty($filters['first_name'])) {
            $query->where('first_name', 'like', '%' . $filters['first_name'] . '%');
        }
        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'like', '%' . $filters['last_name'] . '%');
        }
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }
        if (array_key_exists('specialization', $filters)) {
            if ($filters['specialization'] === null || strtolower((string)$filters['specialization']) === 'null') {
                $query->whereNull('specialization');
            } else {
                $query->where('specialization', 'like', '%' . $filters['specialization'] . '%');
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