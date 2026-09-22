<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityChecklist extends Model
{
    protected $fillable = ['activity_id','item','is_checked','note'];
    protected $casts = ['is_checked' => 'boolean'];
    public function activity() { return $this->belongsTo(Activity::class); }
}
