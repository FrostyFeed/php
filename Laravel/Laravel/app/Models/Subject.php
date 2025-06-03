<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description'];
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
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (array_key_exists('description', $filters)) {
            if ($filters['description'] === null || strtolower((string)$filters['description']) === 'null') {
                $query->whereNull('description');
            } else {
                $query->where('description', 'like', '%' . $filters['description'] . '%');
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