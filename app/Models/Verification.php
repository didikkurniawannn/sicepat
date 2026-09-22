<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Verification extends Model
{
    protected $fillable = ['activity_id','user_id','role_at_time','decision','note'];
    public function activity() { return $this->belongsTo(Activity::class); }
    public function user() { return $this->belongsTo(User::class); }
}
