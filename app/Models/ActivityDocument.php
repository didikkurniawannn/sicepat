<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityDocument extends Model
{
    protected $fillable = ['activity_id','name','type','file_path','uploaded_by'];
    public function activity() { return $this->belongsTo(Activity::class); }
}
