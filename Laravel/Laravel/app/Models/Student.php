<?php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Student extends Model {
    use HasFactory;
    protected $fillable = ['first_name', 'last_name', 'date_of_birth', 'class_group'];
     protected $casts = [ 
        'date_of_birth' => 'date',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    public function grades() { return $this->hasMany(Grade::class); }
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

        if (array_key_exists('date_of_birth', $filters)) {
            if ($filters['date_of_birth'] === null || strtolower((string)$filters['date_of_birth']) === 'null') {
                $query->whereNull('date_of_birth');
            } elseif (!empty($filters['date_of_birth'])) {
                 $query->whereDate('date_of_birth', Carbon::parse($filters['date_of_birth'])->toDateString());
            }
        }
        if (!empty($filters['date_of_birth_from'])) {
            $query->whereDate('date_of_birth', '>=', Carbon::parse($filters['date_of_birth_from'])->toDateString());
        }
        if (!empty($filters['date_of_birth_to'])) {
            $query->whereDate('date_of_birth', '<=', Carbon::parse($filters['date_of_birth_to'])->toDateString());
        }
        if (isset($filters['date_of_birth_is_null']) && filter_var($filters['date_of_birth_is_null'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('date_of_birth');
        }


        if (array_key_exists('class_group', $filters)) {
            if ($filters['class_group'] === null || strtolower((string)$filters['class_group']) === 'null') {
                $query->whereNull('class_group');
            } else {
                $query->where('class_group', 'like', '%' . $filters['class_group'] . '%');
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